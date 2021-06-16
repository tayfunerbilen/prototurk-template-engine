<?php

class PtEngine
{

    public const VIEW_SUFFIX = 'ptengine';
    public array $config;
    public string $view;
    public string $viewName;
    public string $viewPath;
    public array $data;
    public array $sections = [];
    public array $directives = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function view(string $viewName, array $data = [], $extends = false)
    {
        extract($data);

        if (!$extends) {
            $this->viewName = $viewName;
            $this->viewPath = $this->config['views'] . '/' . $this->parseViewName($viewName);
            $this->data = $data;
        }

        $viewPath = $this->config['views'] . '/' . $this->parseViewName($viewName);

        if (!file_exists($viewPath)) {
            throw new Error('View dosyası bulunamadı = ' . $viewPath);
        }

        $this->view = file_get_contents($viewPath);
        $this->parse();


        $cachePath = $this->config['cache'] . '/' . md5($this->viewName) . '.cache.php';
        if (!file_exists($cachePath)) {
            file_put_contents($cachePath, $this->view);
        }

        if (
            filemtime($cachePath) < filemtime($viewPath) ||
            filemtime($cachePath) < filemtime($this->viewPath)
        ) {
            echo '<!-- cache yenilendi -->';
            file_put_contents($cachePath, $this->view);
        }

        if (!$extends) {

            ob_start();
            require $cachePath;
            return ob_get_clean();
        }

    }

    /**
     * @param $viewName
     * @return string
     */
    public function parseViewName(string $viewName): string
    {
        $viewName = str_replace('.', '/', $viewName);
        return $viewName . '.' . ($this->config['suffix'] ?? self::VIEW_SUFFIX) . '.php';
    }

    public function parse(): void
    {
        $this->parseIncludes();
        $this->parsePHP();
        $this->parseVariables();
        $this->parseForEach();
        $this->parseSections();
        $this->parseExtends();
        $this->parseYields();
        $this->customDirectives();
    }

    public function parsePHP()
    {
        $this->view = preg_replace_callback('/@php(.*?)@endphp/s', function($code) {
            return '<?php ' . $code[1] . ' ?>';
        }, $this->view);
    }

    public function parseVariables(): void
    {
        $this->view = preg_replace_callback('/{{(.*?)}}/', function ($variable) {
            return '<?=' . trim($variable[1]) . '?>';
        }, $this->view);
    }

    public function parseForEach(): void
    {
        $this->view = preg_replace_callback('/@foreach\((.*?)\)/', function ($expression) {
            return '<?php foreach(' . $expression[1] . '): ?>';
        }, $this->view);

        $this->view = preg_replace('/@endforeach/', '<?php endforeach; ?>', $this->view);
    }

    public function parseIncludes(): void
    {
        $this->view = preg_replace_callback('/@include\(\'(.*?)\'\)/', function ($viewName) {
            return file_get_contents($this->config['views'] . '/' . $this->parseViewName($viewName[1]));
        }, $this->view);
    }

    public function parseExtends(): void
    {
        $this->view = preg_replace_callback('/@extends\(\'(.*?)\'\)/', function ($viewName) {
            $this->view($viewName[1], $this->data, true);
            return '';
        }, $this->view);
    }

    public function parseYields(): void
    {
        $this->view = preg_replace_callback('/@yield\(\'(.*?)\'\)/', function ($yieldName) {
            return $this->sections[$yieldName[1]] ?? '';
        }, $this->view);
    }

    public function parseSections(): void
    {

        $this->view = preg_replace_callback('/@section\(\'(.*?)\', \'(.*?)\'\)/', function ($sectionDetail) {
            $this->sections[$sectionDetail[1]] = $sectionDetail[2];
            return '';
        }, $this->view);

        $this->view = preg_replace_callback('/@section\(\'(.*?)\'\)(.*?)@endsection/s', function ($sectionName) {
            $this->sections[$sectionName[1]] = $sectionName[2];
            return '';
        }, $this->view);
    }

    public function directive($key, $callback)
    {
        $this->directives[$key] = $callback;
    }

    public function customDirectives()
    {
        foreach ($this->directives as $key => $callback) {
            $this->view = preg_replace_callback('/@' . $key . '(\(\'(.*?)\'\)|)/', function($expression) use ($callback) {
                return call_user_func($callback, $expression[2] ?? null);
            }, $this->view);
        }
    }

}