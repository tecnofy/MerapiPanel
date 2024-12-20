<?php

namespace MerapiPanel\Exception;

use MerapiPanel\Utility\Http\Response;
use MerapiPanel\Views\View;
use Throwable;
use Twig\Loader\FilesystemLoader;


// $__v = 1;
// ini_set('display_errors', $__v);
// ini_set('display_startup_errors', $__v);
// error_reporting($__v);



class Catcher
{

    private static $instance = null;

    private function __construct()
    {

        register_shutdown_function([$this, 'shutdown']);
        set_exception_handler([$this, "exception"]);

    }

    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function shutdown()
    {

        $error = error_get_last();
        if (empty($error)) {
            return;
        }

        $errorString = $error['message'];
        $message = self::extractMessageFromString($errorString);
        $stackTrace = self::extractTracerFromString($errorString, $error['file']);
        $snippet = self::getCodeSnippet($error['file'], $error['line']);

        $type = "Fatal Error";
        if (self::extractTypeFromString($errorString)) {
            $type = self::extractTypeFromString($errorString);
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }


        echo self::send(...[
            "file" => $error['file'],
            "line" => $error['line'],
            "message" => $message,
            "code" => 500,
            "type" => $type,
            "trace" => $stackTrace,
            "snippet" => $snippet
        ]);

        exit();
    }


    public function exception(Throwable $e)
    {

        echo self::send(...[
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "code" => (int)$e->getCode(),
            "type" => basename($e::class),
            "trace" => self::transformTracerFromArray($e->getTrace()),
            'snippet' => self::getCodeSnippet($e->getFile(), $e->getLine()),
        ]);
    }


    private static function send($file = "", $line = 0, $message = "", $code = 500, $type = "", $trace = [], $snippet = ""):string
    {
        // transform ti absolute path
        if (isset($file)) {
            $file = str_replace($_ENV['__MP_CWD__'], "{CWD}", $file);
        }
        // error code can be 0
        if (!isset($code) || $code == 0) {
            $code = 500;
        }

        $view = View::getInstance();
        $view->setLoader(new FilesystemLoader(__DIR__ . "/Views"));

        $template = "error.html.twig";

        $loader = $view->getLoader();
        if ($loader->exists("{$code}.html.twig")) {
            $template = "{$code}.html.twig";
        }

        $response = new Response(
            $view->load($template)->render([
                "error" => [
                    "file" => $file,
                    "line" => $line,
                    "message" => $message,
                    "code" => $code,
                    "type" => $type,
                    "trace" => $trace,
                    "snippet" => $snippet
                ]
            ]),
            $code
        );
        $response->setHeader("Content-Type", "text/html");
        return (string)$response;
    }








    private static function extractTypeFromString($errorString)
    {
        preg_match('/^(.*?)\:/i', $errorString, $matches);

        if (isset($matches[1])) {
            return trim(preg_replace('/^Uncaught\s/i', '', $matches[1]));
        }

        return null;
    }




    private static function extractMessageFromString($errorString)
    {
        $stackTracePosition = strpos($errorString, 'Stack trace:');
        if ($stackTracePosition !== false) {
            return preg_replace("/^Uncaught\s" . implode("\\\\", explode("\\", self::extractTypeFromString($errorString) . ':')) . "\s/i", '', str_replace(trim(substr($errorString, $stackTracePosition)), '', $errorString));
        } else {
            return $errorString;
        }
    }



    private static function transformTracerFromArray(array $traceData = [])
    {

        $tracer = [];

        foreach ($traceData as $trace) {
            if (isset($trace['file'])) {
                $trace['file'] = str_replace($_ENV['__MP_CWD__'], "{CWD}", $trace['file']);
            }
            $tracer[] = [
                "file" => $trace['file'] ?? $trace['class'] ?? '',
                "line" => $trace['line'] ?? null,
                "function" => $trace['function']
            ];
        }

        $reversed = array_reverse($tracer);
        return $reversed;
    }


    private static function extractTracerFromString($errorString, $errorFile): array
    {

        preg_match('/Stack trace:(.*)/s', $errorString, $matches);
        $stackTrace = isset($matches[1]) ? array_values(array_filter(array_map(function ($trace) use (&$isEnd) {
            $trace = trim(preg_replace('/^#\d\s+/', '', $trace));
            if ($trace == "{main}" || $trace == "thrown")
                return "";
            $traceData = self::splitTraceDataFromString($trace);
            return $traceData;
        }, explode("\n", $matches[1])))) : [];

        $reversed = array_reverse($stackTrace);
        array_pop($reversed);

        $reversed = array_slice($reversed, 0, array_search($errorFile, array_column($reversed, "file")) + 1);

        return $reversed;
    }




    private static function splitTraceDataFromString($traceString)
    {
        $matches = [];

        // Extracting file more robustly, accounting for different path formats
        preg_match('/.*\.php|\[(.*)\]/', $traceString, $matches);
        $file = $matches[0] ?? '';

        // Extracting line
        preg_match('/\((\d+)\)/', $traceString, $matches);
        $line = $matches[1] ?? '';

        // Extracting class with improved pattern to handle backslashes in namespaces
        preg_match('/:\s*([^->]+)->/', $traceString, $matches);
        $class = str_replace("\\\\", "\\", $matches[1] ?? '');

        // Extracting method with a pattern that captures method names accurately
        preg_match('/->(\w+)\(/', $traceString, $matches);
        $method = $matches[1] ?? '';

        return [
            "file" => $file,
            "line" => $line,
            "class" => $class,
            "method" => $method
        ];
    }



    private static function getCodeSnippet(string $file, int $line, int $maxLines = 10)
    {

        if (!file_exists($file)) {
            return 'File not found: ' . $file;
        }

        $half = intval($maxLines / 2);

        $advantage = 0;
        if ($half < ($maxLines / 2)) {
            $advantage += (($maxLines / 2) - $half) * 2;
        }

        $lines = file($file);
        $start = max(0, ($line - $half) - $advantage);
        $end = min(count($lines), $line + $half);

        $snippet = '<table class="code-snippet">';
        for ($i = $start; $i < $end; $i++) {

            $lineNumber = $i + 1;
            $lineContent = htmlspecialchars($lines[$i]);

            $tableRow = '<tr' . ($lineNumber == $line ? ' class="highlight"' : '') . '>';
            $tableRow .= '<td class="line-number">' . $lineNumber . '</td>';
            $tableRow .= '<td class="line-content">' . $lineContent . '</td>';
            $snippet .= $tableRow . '</tr>';
        }
        $snippet .= '</table>';

        return $snippet;
    }

}