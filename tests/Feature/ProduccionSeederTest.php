<?php

namespace Tests\Feature;

use App\Modules\Bienestar\Database\Seeders\BienestarProduccionSeeder;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class ProduccionSeederTest extends TestCase
{
    public function test_produccion_seeder_rejects_empty_password(): void
    {
        putenv('INITIAL_ADMIN_PASSWORD=');
        unset($_ENV['INITIAL_ADMIN_PASSWORD'], $_SERVER['INITIAL_ADMIN_PASSWORD']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Debe definir la variable de entorno INITIAL_ADMIN_PASSWORD');

        $seeder = new BienestarProduccionSeeder();
        $seeder->run();
    }

    public function test_produccion_seeder_rejects_default_demo_password(): void
    {
        putenv('INITIAL_ADMIN_PASSWORD=password123');
        $_ENV['INITIAL_ADMIN_PASSWORD'] = 'password123';
        $_SERVER['INITIAL_ADMIN_PASSWORD'] = 'password123';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Por razones de seguridad, la contrasena de produccion no puede ser la clave predeterminada de prueba "password123".');

        $seeder = new BienestarProduccionSeeder();
        $seeder->run();
    }

    public function test_produccion_seeder_creates_admin_with_secure_password(): void
    {
        $testEmail = 'admin_seeder_prod_test@unitru.edu.pe';
        $securePass = 'ProdAdmin#Strong2026!';

        putenv("INITIAL_ADMIN_EMAIL={$testEmail}");
        $_ENV['INITIAL_ADMIN_EMAIL'] = $testEmail;
        $_SERVER['INITIAL_ADMIN_EMAIL'] = $testEmail;

        putenv("INITIAL_ADMIN_PASSWORD={$securePass}");
        $_ENV['INITIAL_ADMIN_PASSWORD'] = $securePass;
        $_SERVER['INITIAL_ADMIN_PASSWORD'] = $securePass;

        try {
            $seeder = new BienestarProduccionSeeder();
            $seeder->run();

            $admin = Usuario::where('correo', $testEmail)->first();
            $this->assertNotNull($admin, 'El usuario de producción debe crearse.');
            $this->assertTrue(Hash::check($securePass, $admin->contrasena_hash));
            $this->assertFalse(Hash::check('password123', $admin->contrasena_hash));
        } finally {
            // Limpieza
            DB::table('bienestar.usuario')->where('correo', $testEmail)->delete();
            putenv('INITIAL_ADMIN_EMAIL=');
            putenv('INITIAL_ADMIN_PASSWORD=');
            unset($_ENV['INITIAL_ADMIN_EMAIL'], $_SERVER['INITIAL_ADMIN_EMAIL']);
            unset($_ENV['INITIAL_ADMIN_PASSWORD'], $_SERVER['INITIAL_ADMIN_PASSWORD']);
        }
    }
}
