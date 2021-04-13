<?php declare(strict_types=1);
namespace App\Application\Actions\Install;

use App\Application\Actions\Authenticate\AuthenticateAction;
use Google\Authenticator\GoogleQrUrl;
use Psr\Http\Message\ResponseInterface as Response;

const STYLES = <<<EOL
<style>
body {
    margin: 0;
    background-color: dimgrey;
    color: papayawhip;
    display: grid;
    place-items: center;
    height: 100%;
}

main {
    display: grid;
    place-items: center;
}
</style>
EOL;

class InstallAction extends AuthenticateAction {

    /**
     * @inheritDoc
     */
    protected function action(): Response {
        // Create tables
        $this->medoo->create('users', [
            'id' => [
                'serial',
                'primary key',
            ],
            'given_name' => [
                'varchar (255)',
                'not null'
            ],
            'family_name' => [
                'varchar (255)',
                'not null'
            ],
            'email' => [
                'varchar (512)'
            ]
        ]);

        $this->medoo->create('authenticator_secrets', [
            'id' => [
                'serial',
                'primary key'
            ],
            'user_id' => [
                'int',
                'references users (id) on delete cascade'
            ],
            'value' => [
                'varchar (16)',
                'unique',
                'not null'
            ],
            'last_active' => [
                'timestamp'
            ],
            'created' => [
                'timestamp',
                'not null',
                'default NOW()'
            ]
        ]);

        $this->medoo->create('permission_types', [
            'id' => [
                'serial',
                'primary key'
            ],
            'name' => [
                'varchar (255)',
                'unique'
            ]
        ]);

        $this->medoo->create('user_permission', [
            'user_id' => [
                'int',
               'references users (id) on delete cascade'
           ],
            'permission_id' => [
                'int',
                'references permission_types (id) on delete cascade'
           ]
        ]);

        // Check if there is admin user
        // select users.given_name, users.family_name from users, permission_types where name = * limit 1
        $adminUser = $this->medoo->get('users', [
            '[>]user_permission' => [
                'id' => 'user_id'
            ],
            '[>]permission_types' => [
               'user_permission.permission_id' => 'id'
           ]
        ],[
            'users.family_name',
            'users.given_name',
        ], [
            'permission_types.name' => '*'
        ]);

        $this->logger->info('Admin user: ', ['$adminUser' => $adminUser]);

        if (!empty($adminUser)) {
            $this->response->getBody()->write(STYLES);
            $this->response->getBody()->write('<main>');

            $this->response->getBody()->write('<h1>There is admin user already</h1>');
            $this->response->getBody()->write("<em>${adminUser['given_name']} ${adminUser['family_name']}</em>");

            $this->response->getBody()->write('</main>');

            return $this->response;
        }

        // Add admin user
        $this->medoo->insert('users', [
            'given_name' => 'Master',
            'family_name' => 'Admin',
        ]);

        $adminUserId = $this->medoo->id();

        // Create master permission
        $this->medoo->insert('permission_types', [
            'name' => '*'
        ]);

        $masterPermissionId = $this->medoo->id();

        // Add other permissions
        $permissions = ['MANAGE_USER_OTHERS'];
        foreach ($permissions as $permission) {
            $this->medoo->insert('permission_types', [
                'name' => $permission
            ]);
        }

        // Assign permission to master admin
        $this->medoo->insert('user_permission', [
            'user_id' => $adminUserId,
            'permission_id' => $masterPermissionId
        ]);

        // Generate secret for admin
        $secret = $this->authenticator->generateSecret();

        $this->medoo->insert('authenticator_secrets', [
            'user_id' => $adminUserId,
            'value' => $secret,
        ]);

        // Print qr code as response
        $url = GoogleQrUrl::generate('Master Admin', $secret, 'openskiff.pl');

        $this->response->getBody()->write(STYLES);
        $this->response->getBody()->write('<main>');

        $this->response->getBody()->write('<h1>Created Master Admin account</h1>');
        $this->response->getBody()->write("<img alt=\"$secret\" src=\"$url\"/>");
        $this->response->getBody()->write('<p>Scan this code in Google Authenticator</p>');

        $this->response->getBody()->write('</main>');

        return $this->response;
    }
}