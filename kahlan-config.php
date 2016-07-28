<?php
use kahlan\filter\Filter as Filter;
use kahlan\jit\Interceptor;
use kahlan\reporter\Coverage;
use kahlan\jit\patcher\Layer;

Filter::register('mycustom.namespaces', function($chain) {
    $this->_autoloader->addPsr4('Vzaar\\', __DIR__ . '/src');
});

Filter::apply($this, 'namespaces', 'mycustom.namespaces');
Filter::register('api.patchers', function($chain) {
    if (!$interceptor = Interceptor::instance()) {
        return;
    }
    $patchers = $interceptor->patchers();

    return $chain->next();
});
Filter::apply($this, 'patchers', 'api.patchers');

// /**
//  * Initializing a custom coverage reporter
//  */
Filter::register('app.coverage', function($chain) {
    $reporters = $this->reporters();

    if ($this->args()->exists('coverage')) {
        // Limit the Coverage analysis to only a couple of directories only
        $coverage = new Coverage([
                // 'verbosity' => $this->args()->get('coverage'),
                'driver' => new \kahlan\reporter\coverage\driver\Xdebug(),
                'path' => [
                    'src'
                ]
        ]);
        $reporters->add('coverage', $coverage);
    }

    return $reporters;
});

Filter::apply($this, 'coverage', 'app.coverage');