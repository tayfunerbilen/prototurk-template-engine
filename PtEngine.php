<?php

/**
 * Class PtEngine
 * @author Tayfun Erbilen
 * @version 1.0.0
 * @copyright Ananın hak sütü gibi helal olsun xd
 * @date 2021-06-16
 */
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

    /**
     * Viewların çağırıldığı ve tüm işlemlerin başladığı metod
     * @param string $viewName
     * @param array $data
     * @param false $extends
     * @return false|string
     */
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
     * View adını tek bir yerden düzenlemek için ilgili metod
     * @param $viewName
     * @return string
     */
    public function parseViewName(string $viewName): string
    {
        $viewName = str_replace('.', '/', $viewName);
        return $viewName . '.' . ($this->config['suffix'] ?? self::VIEW_SUFFIX) . '.php';
    }

    /**
     * Parse işlemlerini tek bir yerden yönetmek için ilgili metod
     */
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
        $this->parseIfBlocks();
        $this->parseEmpty();
        $this->parseIsset();
        $this->parseForElse();
        $this->parseJSON();
        $this->parseDump();
        $this->parseDd();
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @php
     * @endphp
     */
    public function parsePHP()
    {
        $this->view = preg_replace_callback('/@php(.*?)@endphp/s', function($code) {
            return '<?php ' . $code[1] . ' ?>';
        }, $this->view);
    }

    /**
     * {{ $degisken }} yazılan her yeri <?=$degisken?> olarak değiştiren metod
     */
    public function parseVariables(): void
    {
        $this->view = preg_replace_callback('/{{(.*?)}}/', function ($variable) {
            return '<?=' . trim($variable[1]) . '?>';
        }, $this->view);
    }

    /**
     * Aşağıdaki direktifler için parse işlemi yapar
     * @foreach($array as $item)
     * @endforeach
     */
    public function parseForEach(): void
    {
        $this->view = preg_replace_callback('/@foreach\((.*?)\)/', function ($expression) {
            return '<?php foreach(' . $expression[1] . '): ?>';
        }, $this->view);

        $this->view = preg_replace('/@endforeach/', '<?php endforeach; ?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @include(view.adi)
     */
    public function parseIncludes(): void
    {
        $this->view = preg_replace_callback('/@include\(\'(.*?)\'\)/', function ($viewName) {
            return file_get_contents($this->config['views'] . '/' . $this->parseViewName($viewName[1]));
        }, $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @extends(layout)
     */
    public function parseExtends(): void
    {
        $this->view = preg_replace_callback('/@extends\(\'(.*?)\'\)/', function ($viewName) {
            $this->view($viewName[1], $this->data, true);
            return '';
        }, $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @yield(section.adi)
     */
    public function parseYields(): void
    {
        $this->view = preg_replace_callback('/@yield\(\'(.*?)\'\)/', function ($yieldName) {
            return $this->sections[$yieldName[1]] ?? '';
        }, $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @section(section.adi)
     */
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

    /**
     * Yazılan özel direktifleri diziye aktarır
     * @param $key
     * @param $callback
     */
    public function directive($key, $callback)
    {
        $this->directives[$key] = $callback;
    }

    /**
     * Yazılan özel direktifleri parse eder
     */
    public function customDirectives()
    {
        foreach ($this->directives as $key => $callback) {
            $this->view = preg_replace_callback('/@' . $key . '(\(\'(.*?)\'\)|)/', function($expression) use ($callback) {
                return call_user_func($callback, $expression[2] ?? null);
            }, $this->view);
        }
    }

    /**
     * Aşağıdaki direktifler için parse işlemi yapar
     * @if($expr)
     * @elseif($expr)
     * @else
     */
    public function parseIfBlocks()
    {
        $this->view = preg_replace('/@(else|)if\((.*?)\)/', '<?php $1if ($2): ?>', $this->view);
        $this->view = preg_replace('/@else/', '<?php else: ?>', $this->view);
        $this->view = preg_replace('/@endif/', '<?php endif; ?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @empty($var)
     * @endempty
     */
    public function parseEmpty()
    {
        $this->view = preg_replace_callback('/@empty\((.*?)\)/', function($expression) {
            return '<?php if (empty(' . $expression[1] . ')): ?>';
        }, $this->view);
        $this->view = preg_replace('/@endempty/', '<?php endif; ?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @isset($var)
     * @endisset
     */
    public function parseIsset()
    {
        $this->view = preg_replace_callback('/@isset\((.*?)\)/', function($expression) {
            return '<?php if (isset(' . $expression[1] . ')): ?>';
        }, $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @forelse($array as $item)
     * @empty
     * @endforelse
     */
    public function parseForElse()
    {
        $this->view = preg_replace_callback('/@forelse\((.*?)\)/', function ($expression) {
            $data = explode('as', $expression[1]);
            $array = trim($data[0]);
            return '<?php if (isset(' . $array . ') && !empty(' . $array . ')): foreach(' . $expression[1] . '): ?>';
        }, $this->view);

        $this->view = preg_replace('/@empty/', '<?php endforeach; else: ?>', $this->view);

        $this->view = preg_replace('/@endforelse/', '<?php endif; ?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @json($array)
     */
    public function parseJSON()
    {
        $this->view = preg_replace('/@json\((.*?)\)/', '<?=json_encode($1)?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @dump($array)
     */
    public function parseDump()
    {
        $this->view = preg_replace('/@dump\((.*?)\)/', '<?php var_dump($1); ?>', $this->view);
    }

    /**
     * Aşağıdaki direktif için parse işlemi yapar
     * @dd($array)
     */
    public function parseDd()
    {
        $this->view = preg_replace('/@dd\((.*?)\)/', '<?php print_r($1); ?>', $this->view);
    }

}