<?php

namespace App\Models;

use CodeIgniter\Model;

class MelhorEnvioCredentialModel extends Model
{
    protected $table         = 'melhor_envio_credentials';
    protected $returnType    = 'App\Entities\MelhorEnvioCredential';
    protected $allowedFields = [
        'access_token',
        'refresh_token',
        'scope',
        'expires_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function atual()
    {
        return $this->orderBy('id', 'DESC')->first();
    }

    public function salvarTokens(string $accessToken, string $refreshToken, int $expiresInSeconds, ?string $scope = null): int
    {
        $existente = $this->atual();
        $data = [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'scope'         => $scope,
            'expires_at'    => date('Y-m-d H:i:s', time() + $expiresInSeconds),
        ];

        if ($existente) {
            $this->update($existente->id, $data);
            return (int) $existente->id;
        }

        return (int) $this->insert($data, true);
    }
}
