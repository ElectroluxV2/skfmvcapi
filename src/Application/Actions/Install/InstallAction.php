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
                'primary key'
            ],
            'given_name' => [
                'varchar (255)',
                'not null'
            ],
            'family_name' => [
                'varchar (255)',
                'not null'
            ],
            'permission_level' => [
                'integer',
                'DEFAULT 0'
            ]
        ]);

        $this->medoo->create('authenticator_secrets', [
            'id' => [
                'serial',
                'primary key'
            ],
            'user_id' => [
                'integer',
                'references users (id)'
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
                'DEFAULT NOW()'
            ]
        ]);

        // Check if there is admin user
        $adminUser = $this->medoo->get('users', [
            'given_name',
            'family_name'
        ], [
           'permission_level[>=]' => 100
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
            'permission_level' => 100
        ]);

        // Generate secret for admin
        $adminId = $this->medoo->id();
        $secret = $this->authenticator->generateSecret();

        $this->medoo->insert('authenticator_secrets', [
            'user_id' => $adminId,
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