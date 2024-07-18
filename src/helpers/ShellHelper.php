<?php

namespace Imccc\Snail\Helpers;

class ShellHelper
{
    /**
     * 检查禁用
     *
     * @return bool
     */
    public static function isDisabled()
    {
        return ini_get('disable_functions') != '';
    }

    /**
     * 检查某一个命令是否禁用
     *
     * @param string $command
     * @return bool
     */
    public static function isCommandDisabled($command)
    {
        $disabled = explode(',', ini_get('disable_functions'));
        return in_array($command, $disabled);
    }

    /**
     * 执行 shell 命令
     *
     * @param string $command
     * @param string $output
     * @param int $returnCode
     * @return bool
     */
    public static function run($command, &$output = null, &$returnCode = null)
    {
        // 验证和过滤输入
        if (!is_string($command)) {
            throw new InvalidArgumentException("Invalid command");
        }

        // 转义命令参数
        $escapedCommand = escapeshellcmd($command);

        // 执行命令
        $output = shell_exec($escapedCommand);
        $returnCode = 0; // shell_exec 不提供返回码

        return $output !== null;
    }

    /**
     * 运行 exec 命令
     *
     * @param string $command
     * @param string $output
     * @param int $returnCode
     * @return bool
     */
    public static function exec($command, &$output = null, &$returnCode = null)
    {
        // 验证和过滤输入
        if (!is_string($command)) {
            throw new InvalidArgumentException("Invalid command");
        }

        // 执行命令
        exec($command, $output, $returnCode);

        return $output !== null;
    }

    /**
     * 运行 shell_exec 命令
     *
     * @param string $command
     * @param string $output
     * @param int $returnCode
     * @return bool
     */
    public static function shell($command, &$output = null, &$returnCode = null)
    {
        // 验证和过滤输入
        if (!is_string($command)) {
            throw new InvalidArgumentException("Invalid command");
        }

        // 转义命令参数
        $escapedCommand = escapeshellarg($command);

        // 执行命令
        $output = shell_exec($escapedCommand);
        $returnCode = 0; // shell_exec 不提供返回码

        return $output !== null;
    }
}
