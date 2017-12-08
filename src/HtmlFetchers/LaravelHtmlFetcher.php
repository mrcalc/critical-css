<?php

namespace Alfheim\CriticalCss\HtmlFetchers;

use Closure;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

/**
 * This implementation fetches HTML for a given URI by mocking a Request and
 * letting a new instance of the Laravel Application handle it.
 */
class LaravelHtmlFetcher implements HtmlFetcherInterface
{
    use MakesHttpRequests;

    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app = null;
    protected $baseUrl = '';

    /**
     * Create a new instance.
     *
     * @param  \Closure $appMaker
     *
     * @return void
     */
    public function __construct(Closure $appMaker)
    {
        $this->app = $appMaker();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($uri)
    {
        $response = $this->call('GET', $uri);

        if (!$response->isOk()) {
			
            throw new HtmlFetchingException(
                sprintf('Invalid response from URI [%s].\n[%s]', $uri, $response->exception->getTraceAsString())
            );
        }

        return $this->stripCss($response->getContent());
    }

    /**
     * Remove any existing inlined critical-path CSS that has been generated
     * previously. Old '<style>' tags should be tagged with a `data-inline`
     * attribute.
     *
     * @param  string $html
     *
     * @return string
     */
    protected function stripCss($html)
    {
        return preg_replace('/\<style data-inlined\>.*\<\/style\>/s', '', $html);
    }
}
