<?php

namespace Imccc\Snail\Services;

class RbacService
{
    private $container;
    private $sqlService;
    private $userTable;
    private $roleTable;
    private $permissionTable;
    private $userRoleTable;
    private $rolePermissionTable;
    private $groupTable;
    private $groupPermissionTable;
    private $resourceTable;
    private $resourcePermissionTable;
    private $logger;
    private $cacheService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->sqlService = $this->container->resolve('SqlService');
        $this->logger = $this->container->resolve('LoggerService');
        $this->cacheService = $this->container->resolve('CacheService');

        $this->userTable = $this->sqlService->setTable('users');
        $this->roleTable = $this->sqlService->setTable('roles');
        $this->permissionTable = $this->sqlService->setTable('permissions');
        $this->userRoleTable = $this->sqlService->setTable('user_roles');
        $this->rolePermissionTable = $this->sqlService->setTable('role_permissions');
        $this->groupTable = $this->sqlService->setTable('permission_groups');
        $this->groupPermissionTable = $this->sqlService->setTable('group_permissions');
        $this->resourceTable = $this->sqlService->setTable('resources');
        $this->resourcePermissionTable = $this->sqlService->setTable('resource_permissions');
    }

    /**
     * 添加用户
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @return bool 返回操作结果
     */
    public function addUser($username, $password, $email)
    {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $data = [
                'username' => $username,
                'password_hash' => $passwordHash,
                'email' => $email,
            ];
            $this->sqlService->insert($this->userTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 用户认证
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|null 返回用户数据或null
     */
    public function authenticateUser($username, $password)
    {
        try {
            $condition = 'username = :username';
            $params = [':username' => $username];
            $user = $this->sqlService->select($this->userTable, ['*'], $condition, $params);

            if ($user && password_verify($password, $user['password_hash'])) {
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error authenticating user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 添加角色
     * @param string $name 角色名称
     * @param string $description 角色描述
     * @return bool 返回操作结果
     */
    public function addRole($name, $description)
    {
        try {
            $data = [
                'name' => $name,
                'description' => $description,
            ];
            $this->sqlService->insert($this->roleTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding role: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 添加权限
     * @param string $name 权限名称
     * @param string $description 权限描述
     * @return bool 返回操作结果
     */
    public function addPermission($name, $description)
    {
        try {
            $data = [
                'name' => $name,
                'description' => $description,
            ];
            $this->sqlService->insert($this->permissionTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 将角色分配给用户
     * @param int $userId 用户ID
     * @param int $roleId 角色ID
     * @return bool 返回操作结果
     */
    public function assignRoleToUser($userId, $roleId)
    {
        try {
            $data = [
                'user_id' => $userId,
                'role_id' => $roleId,
            ];
            $this->sqlService->insert($this->userRoleTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error assigning role to user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 将权限分配给角色
     * @param int $roleId 角色ID
     * @param int $permissionId 权限ID
     * @return bool 返回操作结果
     */
    public function addPermissionToRole($roleId, $permissionId)
    {
        try {
            $data = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ];
            $this->sqlService->insert($this->rolePermissionTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding permission to role: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 添加权限组
     * @param string $name 权限组名称
     * @param string $description 权限组描述
     * @return bool 返回操作结果
     */
    public function addPermissionGroup($name, $description)
    {
        try {
            $data = [
                'name' => $name,
                'description' => $description,
            ];
            $this->sqlService->insert($this->groupTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding permission group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 将权限分配给权限组
     * @param int $groupId 权限组ID
     * @param int $permissionId 权限ID
     * @return bool 返回操作结果
     */
    public function addPermissionToGroup($groupId, $permissionId)
    {
        try {
            $data = [
                'group_id' => $groupId,
                'permission_id' => $permissionId,
            ];
            $this->sqlService->insert($this->groupPermissionTable, $data);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding permission to group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 检查用户是否具有某个权限组的权限
     * @param int $userId 用户ID
     * @param string $groupName 权限组名称
     * @return bool 返回用户是否具有权限组的权限
     */
    public function userHasPermissionGroup($userId, $groupName)
    {
        try {
            // 获取用户的角色
            $roles = $this->getUserRoles($userId);
            $roleIds = array_column($roles, 'role_id');

            if (!$roleIds) {
                return false;
            }

            // 获取角色的权限组
            $groupCondition = 'role_id IN (' . implode(',', array_fill(0, count($roleIds), '?')) . ')';
            $groupParams = $roleIds;
            $groups = $this->sqlService->select('role_permission_groups', ['group_id'], $groupCondition, $groupParams);

            if (!$groups) {
                return false;
            }

            $groupIds = array_column($groups, 'group_id');

            // 获取权限组的权限
            $permissionCondition = 'group_id IN (' . implode(',', array_fill(0, count($groupIds), '?')) . ')';
            $permissionParams = $groupIds;
            $permissions = $this->sqlService->select($this->groupPermissionTable, ['permission_id'], $permissionCondition, $permissionParams);

            if (!$permissions) {
                return false;
            }

            $permissionIds = array_column($permissions, 'permission_id');

            // 获取权限名称
            $permissionCondition = 'id IN (' . implode(',', array_fill(0, count($permissionIds), '?')) . ')';
            $permissionParams = $permissionIds;
            $permissionData = $this->sqlService->select($this->permissionTable, ['name'], $permissionCondition, $permissionParams);

            if (!$permissionData) {
                return false;
            }

            $permissionNames = array_column($permissionData, 'name');
            return in_array($groupName, $permissionNames);
        } catch (\Exception $e) {
            $this->logger->error('Error checking user permission group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 检查用户是否具有特定权限
     * @param int $userId 用户ID
     * @param string $permissionName 权限名称
     * @return bool 返回用户是否具有权限
     */
    public function userHasPermission($userId, $permissionName)
    {
        try {
            $cacheKey = "user_{$userId}_permissions";
            $cachedPermissions = $this->cacheService->get($cacheKey);

            if ($cachedPermissions === false) {
                // 获取用户的角色
                $condition = 'user_id = :user_id';
                $params = [':user_id' => $userId];
                $roles = $this->sqlService->select($this->userRoleTable, ['role_id'], $condition, $params);

                if (!$roles) {
                    return false;
                }

                // 获取角色的权限
                $roleIds = array_column($roles, 'role_id');
                $roleCondition = 'role_id IN (' . implode(',', array_fill(0, count($roleIds), '?')) . ')';
                $roleParams = $roleIds;

                $permissions = $this->sqlService->select($this->rolePermissionTable, ['permission_id'], $roleCondition, $roleParams);

                if (!$permissions) {
                    return false;
                }

                $permissionIds = array_column($permissions, 'permission_id');

                // 获取权限名称
                $permissionCondition = 'id IN (' . implode(',', array_fill(0, count($permissionIds), '?')) . ')';
                $permissionParams = $permissionIds;

                $permissionData = $this->sqlService->select($this->permissionTable, ['name'], $permissionCondition, $permissionParams);

                if (!$permissionData) {
                    return false;
                }

                $cachedPermissions = array_column($permissionData, 'name');
                $this->cacheService->set($cacheKey, $cachedPermissions);
            }

            return in_array($permissionName, $cachedPermissions);
        } catch (\Exception $e) {
            $this->logger->error('Error checking user permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 检查用户是否具有特定角色，考虑角色层次。
     */
    public function userHasRole($userId, $roleName)
    {
        try {
            $roles = $this->getUserRoles($userId);
            $roleHierarchy = $this->getRoleHierarchy();

            foreach ($roles as $role) {
                if ($role['name'] === $roleName) {
                    return true;
                }
                if (isset($roleHierarchy[$role['name']]) && in_array($roleName, $roleHierarchy[$role['name']])) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('检查用户角色时出错: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 基于某些条件动态分配角色给用户。
     */
    public function assignRoleBasedOnCondition($userId, $condition)
    {
        try {
            // 在这里定义你的条件逻辑
            $roleId = $this->determineRoleBasedOnCondition($condition);
            if ($roleId) {
                return $this->assignRoleToUser($userId, $roleId);
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('基于条件分配角色时出错: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 动态策略：为用户分配权限。
     * @param int $userId 用户ID
     * @param string $resource 资源名称
     * @param string $action 动作
     * @return bool 返回用户是否具有该权限
     */
    public function dynamicPermissionCheck($userId, $resource, $action)
    {
        try {
            $dynamicPermissions = $this->getDynamicPermissions($userId);

            foreach ($dynamicPermissions as $permission) {
                if ($permission['resource'] === $resource && $permission['action'] === $action) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error in dynamic permission check: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取用户的动态权限
     * @param int $userId 用户ID
     * @return array 返回动态权限列表
     */
    private function getDynamicPermissions($userId)
    {
        // 根据具体需求获取动态权限
        // 示例：从数据库中获取动态权限
        $condition = 'user_id = :user_id';
        $params = [':user_id' => $userId];
        return $this->sqlService->select('dynamic_permissions', ['resource', 'action'], $condition, $params);
    }

    /**
     * 获取用户角色
     * @param int $userId 用户ID
     * @return array 返回用户角色列表
     */
    private function getUserRoles($userId)
    {
        $condition = 'user_id = :user_id';
        $params = [':user_id' => $userId];
        return $this->sqlService->select($this->userRoleTable, ['role_id'], $condition, $params);
    }

    /**
     * 获取角色层次结构
     * @return array 返回角色层次结构
     */
    private function getRoleHierarchy()
    {
        // 根据具体需求定义角色层次结构
        return [
            'admin' => ['manager', 'user'],
            'manager' => ['user'],
            'user' => [],
        ];
    }

    /**
     * 根据条件确定角色
     * @param mixed $condition 条件
     * @return int|null 返回角色ID或null
     */
    private function determineRoleBasedOnCondition($condition)
    {
        // 根据具体需求定义条件逻辑
        if ($condition === 'special') {
            return 2; // 示例角色ID
        }
        return null;
    }
}
