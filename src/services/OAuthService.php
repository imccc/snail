<?php

namespace Imccc\Snail\Services;

class AuthService
{
    protected $container;
    private $sqlService;  // 数据库服务实例
    private $userTable;   // 用户表
    private $clientTable; // 客户端表
    private $tokenTable;  // 访问令牌表
    private $authCodeTable; // 授权码表
    private $refreshTokenTable; // 刷新令牌表
    private $logger;  // 日志服务实例

    // 构造函数，初始化数据库服务和日志服务
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->sqlService = $this->container->resolve('SqlService');
        $this->logger = $this->container->resolve('LoggerService');
    
        $this->userTable = $this->sqlService->setTable('users'); // 设置用户表
        $this->clientTable = $this->sqlService->setTable('clients'); // 设置客户端表
        $this->tokenTable = $this->sqlService->setTable('tokens'); // 设置访问令牌表
        $this->authCodeTable = $this->sqlService->setTable('auth_codes'); // 设置授权码表
        $this->refreshTokenTable = $this->sqlService->setTable('refresh_tokens'); // 设置刷新令牌表
    }

    /**
     * 用户认证：验证用户名和密码
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|null 返回用户信息数组或null
     */
    public function authenticateUser($username, $password)
    {
        try {
            $condition = 'username = :username'; // 查询条件
            $params = [':username' => $username]; // 查询参数
            $user = $this->sqlService->select($this->userTable, ['*'], $condition, $params); // 从数据库查询用户

            // 验证密码
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error authenticating user: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 用户注册
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 电子邮件
     * @return bool|null 返回true表示成功，null表示失败
     */
    public function registerUser($username, $password, $email)
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // 哈希密码
            $data = [
                'username' => $username,
                'password' => $hashedPassword,
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            return $this->sqlService->insert($this->userTable, $data); // 插入用户数据
        } catch (\Exception $e) {
            $this->logger->error('Error registering user: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 更新用户信息
     * @param int $userId 用户ID
     * @param array $data 要更新的数据数组
     * @return bool|null 返回true表示成功，null表示失败
     */
    public function updateUser($userId, $data)
    {
        try {
            $condition = 'id = :id'; // 查询条件
            $params = [':id' => $userId];

            return $this->sqlService->update($this->userTable, $data, $condition, $params); // 更新用户数据
        } catch (\Exception $e) {
            $this->logger->error('Error updating user: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 重置密码
     * @param int $userId 用户ID
     * @param string $newPassword 新密码
     * @return bool|null 返回true表示成功，null表示失败
     */
    public function resetPassword($userId, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // 哈希新密码
            $data = [
                'password' => $hashedPassword,
            ];
            $condition = 'id = :id'; // 查询条件
            $params = [':id' => $userId];

            return $this->sqlService->update($this->userTable, $data, $condition, $params); // 更新用户密码
        } catch (\Exception $e) {
            $this->logger->error('Error resetting password: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 获取客户端信息
     * @param string $clientId 客户端ID
     * @return array|null 返回客户端信息数组或null
     */
    public function getClient($clientId)
    {
        try {
            $condition = 'client_id = :client_id'; // 查询条件
            $params = [':client_id' => $clientId]; // 查询参数
            return $this->sqlService->select($this->clientTable, ['*'], $condition, $params); // 从数据库查询客户端
        } catch (\Exception $e) {
            $this->logger->error('Error getting client: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 验证客户端凭据
     * @param string $clientId 客户端ID
     * @param string $clientSecret 客户端密钥
     * @return bool 返回true表示验证通过，false表示验证失败
     */
    public function validateClient($clientId, $clientSecret)
    {
        $client = $this->getClient($clientId); // 获取客户端信息
        if ($client && $client['client_secret'] === $clientSecret) { // 验证客户端密钥
            return true;
        }
        return false;
    }

    /**
     * 生成访问令牌
     * @param string $clientId 客户端ID
     * @param int $userId 用户ID
     * @param int $expiresIn 令牌有效期（秒）
     * @return string|null 返回生成的令牌或null
     */
    public function generateAccessToken($clientId, $userId, $expiresIn)
    {
        try {
            $token = bin2hex(random_bytes(40)); // 生成随机令牌
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn); // 设置令牌过期时间

            $data = [
                'access_token' => $token,
                'client_id' => $clientId,
                'user_id' => $userId,
                'expires_at' => $expiresAt,
            ];

            $this->sqlService->insert($this->tokenTable, $data); // 将令牌插入数据库
            return $token;
        } catch (\Exception $e) {
            $this->logger->error('Error generating access token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 验证访问令牌
     * @param string $token 访问令牌
     * @return array|null 返回令牌数据数组或null
     */
    public function validateAccessToken($token)
    {
        try {
            $condition = 'access_token = :access_token AND expires_at > :now'; // 查询条件
            $params = [
                ':access_token' => $token,
                ':now' => date('Y-m-d H:i:s'),
            ];

            return $this->sqlService->select($this->tokenTable, ['*'], $condition, $params); // 从数据库查询令牌
        } catch (\Exception $e) {
            $this->logger->error('Error validating access token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 撤销访问令牌
     * @param string $token 访问令牌
     */
    public function revokeAccessToken($token)
    {
        try {
            $condition = 'access_token = :access_token'; // 查询条件
            $params = [':access_token' => $token];

            $this->sqlService->delete($this->tokenTable, $condition, $params); // 从数据库删除令牌
        } catch (\Exception $e) {
            $this->logger->error('Error revoking access token: ' . $e->getMessage()); // 记录错误日志
        }
    }

    /**
     * 生成授权码
     * @param string $clientId 客户端ID
     * @param int $userId 用户ID
     * @param int $expiresIn 授权码有效期（秒）
     * @return string|null 返回生成的授权码或null
     */
    public function generateAuthCode($clientId, $userId, $expiresIn)
    {
        try {
            $authCode = bin2hex(random_bytes(20)); // 生成随机授权码
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn); // 设置授权码过期时间

            $data = [
                'auth_code' => $authCode,
                'client_id' => $clientId,
                'user_id' => $userId,
                'expires_at' => $expiresAt,
            ];

            $this->sqlService->insert($this->authCodeTable, $data); // 将授权码插入数据库
            return $authCode;
        } catch (\Exception $e) {
            $this->logger->error('Error generating auth code: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 验证授权码
     * @param string $authCode 授权码
     * @return array|null 返回授权码数据数组或null
     */
    public function validateAuthCode($authCode)
    {
        try {
            $condition = 'auth_code = :auth_code AND expires_at > :now'; // 查询条件
            $params = [
                ':auth_code' => $authCode,
                ':now' => date('Y-m-d H:i:s'),
            ];

            return $this->sqlService->select($this->authCodeTable, ['*'], $condition, $params); // 从数据库查询授权码
        } catch (\Exception $e) {
            $this->logger->error('Error validating auth code: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 撤销授权码
     * @param string $authCode 授权码
     */
    public function revokeAuthCode($authCode)
    {
        try {
            $condition = 'auth_code = :auth_code'; // 查询条件
            $params = [':auth_code' => $authCode];

            $this->sqlService->delete($this->authCodeTable, $condition, $params); // 从数据库删除授权码
        } catch (\Exception $e) {
            $this->logger->error('Error revoking auth code: ' . $e->getMessage()); // 记录错误日志
        }
    }

    /**
     * 生成刷新令牌
     * @param string $clientId 客户端ID
     * @param int $userId 用户ID
     * @param int $expiresIn 刷新令牌有效期（秒）
     * @return string|null 返回生成的刷新令牌或null
     */
    public function generateRefreshToken($clientId, $userId, $expiresIn)
    {
        try {
            $refreshToken = bin2hex(random_bytes(40)); // 生成随机刷新令牌
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn); // 设置刷新令牌过期时间

            $data = [
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'user_id' => $userId,
                'expires_at' => $expiresAt,
            ];

            $this->sqlService->insert($this->refreshTokenTable, $data); // 将刷新令牌插入数据库
            return $refreshToken;
        } catch (\Exception $e) {
            $this->logger->error('Error generating refresh token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 验证刷新令牌
     * @param string $refreshToken 刷新令牌
     * @return array|null 返回刷新令牌数据数组或null
     */
    public function validateRefreshToken($refreshToken)
    {
        try {
            $condition = 'refresh_token = :refresh_token AND expires_at > :now'; // 查询条件
            $params = [
                ':refresh_token' => $refreshToken,
                ':now' => date('Y-m-d H:i:s'),
            ];

            return $this->sqlService->select($this->refreshTokenTable, ['*'], $condition, $params); // 从数据库查询刷新令牌
        } catch (\Exception $e) {
            $this->logger->error('Error validating refresh token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 撤销刷新令牌
     * @param string $refreshToken 刷新令牌
     */
    public function revokeRefreshToken($refreshToken)
    {
        try {
            $condition = 'refresh_token = :refresh_token'; // 查询条件
            $params = [':refresh_token' => $refreshToken];

            $this->sqlService->delete($this->refreshTokenTable, $condition, $params); // 从数据库删除刷新令牌
        } catch (\Exception $e) {
            $this->logger->error('Error revoking refresh token: ' . $e->getMessage()); // 记录错误日志
        }
    }

    /**
     * 刷新访问令牌
     * @param string $refreshToken 刷新令牌
     * @param int $expiresIn 令牌有效期（秒）
     * @return string|null 返回新的访问令牌或null
     */
    public function refreshAccessToken($refreshToken, $expiresIn)
    {
        try {
            $refreshTokenData = $this->validateRefreshToken($refreshToken); // 验证刷新令牌

            if ($refreshTokenData) {
                // 生成新的访问令牌
                $newAccessToken = $this->generateAccessToken($refreshTokenData['client_id'], $refreshTokenData['user_id'], $expiresIn);

                // 更新刷新令牌的过期时间
                $newExpiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
                $condition = 'refresh_token = :refresh_token';
                $params = [
                    ':refresh_token' => $refreshToken,
                ];
                $data = [
                    'expires_at' => $newExpiresAt,
                ];

                $this->sqlService->update($this->refreshTokenTable, $data, $condition, $params); // 更新数据库中的刷新令牌

                return $newAccessToken;
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error refreshing access token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 客户端凭据授权模式
     * @param string $clientId 客户端ID
     * @param int $expiresIn 令牌有效期（秒）
     * @return string|null 返回生成的令牌或null
     */
    public function generateClientCredentialsToken($clientId, $expiresIn)
    {
        try {
            $token = bin2hex(random_bytes(40)); // 生成随机令牌
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn); // 设置令牌过期时间

            $data = [
                'access_token' => $token,
                'client_id' => $clientId,
                'user_id' => null, // 客户端凭据授权不关联用户
                'expires_at' => $expiresAt,
            ];

            $this->sqlService->insert($this->tokenTable, $data); // 将令牌插入数据库
            return $token;
        } catch (\Exception $e) {
            $this->logger->error('Error generating client credentials token: ' . $e->getMessage()); // 记录错误日志
            return null;
        }
    }

    /**
     * 密码授权模式
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $clientId 客户端ID
     * @param string $clientSecret 客户端密钥
     * @param int $expiresIn 令牌有效期（秒）
     * @return string|null 返回生成的访问令牌或null
     */
    public function passwordGrant($username, $password, $clientId, $clientSecret, $expiresIn)
    {
        if ($this->validateClient($clientId, $clientSecret)) { // 验证客户端凭据
            $user = $this->authenticateUser($username, $password); // 验证用户凭据
            if ($user) {
                return $this->generateAccessToken($clientId, $user['id'], $expiresIn); // 生成访问令牌
            }
        }
        return null;
    }
}
