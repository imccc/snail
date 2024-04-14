# ExceptionHandlerTrait

一个为处理和报告错误而设计的 `ExceptionHandlerTrait` 特性。这个特性包含了多种方法来处理异常，显示错误信息，以及记录错误日志。下面是对这个代码的一些关键点的分析和解释：

### 错误处理
- `handleException($exceptionOrErrorCode)` 方法可以接收一个异常对象或一个错误代码，并将其转换为一个异常对象进行处理。它会增加错误计数，显示错误信息，并记录错误日志。它不受report错误处理机制的影响。error_report(0)函数不受影响。

### 错误计数器
- `private static $errorCount = 0;` 定义了一个私有的静态变量用于跟踪处理的错误数量。

### 处理异常
- `handleException($exceptionOrErrorCode)` 方法可以接收一个异常对象或一个错误代码，并将其转换为一个异常对象进行处理。它会增加错误计数，显示错误信息，并记录错误日志。

### 显示调试信息
- `showDebug(String $exception)` 方法根据配置的调试模式（假设存在一个名为 `DEBUG` 的数组定义调试模式），显示错误信息。

### 显示错误信息
- `showError(Throwable $exception)` 根据配置的调试模式，显示错误的详细信息或简单提示。如果是调试模式，会进一步调用 `showDetailedError` 方法显示详细的错误信息。

### 获取样式
- `getStyle()` 和 `getTitleStyle()` 方法提供了错误信息块和标题的 CSS 样式。

### 显示详细错误信息
- `showDetailedError(Throwable $exception)` 方法提取错误码并使用一个错误类型映射表来标识错误类型，展示错误堆栈跟踪。

### 提取错误码
- `extractErrorCode(Throwable $exception)` 方法尝试从异常消息中提取错误码。

### 格式化堆栈跟踪
- `formatStackTrace(array $trace)` 方法格式化堆栈跟踪信息以便于阅读和调试。

### 获取指定文件的源码行
- `getSourceCodeLine(string $filePath, int $lineNumber)` 方法用于获取指定文件和行号的代码行。

### 记录错误日志
- `logError(Throwable $exception)` 方法将错误信息格式化后记录到系统的错误日志。

这个特性能够在开发中提供很大的帮助，尤其是在调试和错误跟踪方面。使用这个特性可以让错误处理更加系统化和规范化，帮助开发者快速定位和解决问题。

## 使用

### 引入
```php
use Imccc\Snail\Exception\ExceptionHandlerTrait;
```

### 使用
```php
// 捕获异常
ExceptionHandlerTrait::handleException($exceptionOrErrorCode);

// 显示调试信息
ExceptionHandlerTrait::showDebug(String $exception);

// 显示错误信息
ExceptionHandlerTrait::showError(Throwable $exception);
```