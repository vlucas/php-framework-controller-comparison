<?php
if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}
$loader->setMode(\Aura\Autoload\Loader::MODE_SILENT);

$loader->add('Hari\Sample\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');

$di->get('router_map')->add('greet', '/', [
    'values' => [
        'controller' => 'greet',
        'action' => 'index',
    ],
]);

$di->get('router_map')->add('post_list', '/post', [
    'values' => [
        'controller' => 'post',
        'action' => 'list',
    ],
]);

$di->get('router_map')->add('post_add', '/post/add', [
    'values' => [
        'controller' => 'post',
        'action' => 'add',
    ],
]);

$di->get('router_map')->add('post_edit', '/post/edit/{:id}', [
    'values' => [
        'controller' => 'post',
        'action' => 'edit',
    ],
]);

$di->get('router_map')->add('post_view', '/post/view/{:id}', [
    'values' => [
        'controller' => 'post',
        'action' => 'view',
    ],
]);

$di->get('router_map')->add('post_delete', '/post/delete/{:id}', [
    'values' => [
        'controller' => 'post',
        'action' => 'delete',
    ],
]);

$di->get('router_map')->add('post_by_tag', '/post/tag/{:tag}', [
    'values' => [
        'controller' => 'post',
        'action' => 'byTag',
    ],
]);

// authors
$di->get('router_map')->add('user_login', '/login', [
    'values' => [
        'controller' => 'user',
        'action' => 'login',
    ],
]);

$di->get('router_map')->add('user_logout', '/logout', [
    'values' => [
        'controller' => 'user',
        'action' => 'logout',
    ],
]);

$di->get('router_map')->add('user_register', '/register', [
    'values' => [
        'controller' => 'user',
        'action' => 'register',
    ],
]);

$di->get('router_map')->add('user_list', '/user/list', [
    'values' => [
        'controller' => 'user',
        'action' => 'list',
    ],
]);

$di->get('router_map')->add('register_sucess', '/registered', [
    'values' => [
        'controller' => 'user',
        'action' => 'sucess',
    ],
]);

$di->get('router_map')->add('form_elements', '/form', [
    'values' => [
        'controller' => 'greet',
        'action' => 'formElements',
    ],
]);

$di->params['Aura\Framework\Web\Controller\Factory']['map']['post'] = 'Hari\Sample\Web\Post\Page';

$di->params['Aura\Framework\Web\Controller\Factory']['map']['greet'] = 'Hari\Sample\Web\Greet\Page';

$di->params['Aura\Framework\Web\Controller\Factory']['map']['user'] = 'Hari\Sample\Web\User\Page';

$signal = $di->get('signal_manager');

$signal->handler(
    'Aura\Framework\Web\Controller\AbstractPage',
    'post_render',
    function ($arg) {
        if (! $arg instanceof Aura\Framework\Web\Asset\Page) {
            $code = new \Hari\Sample\Extension\Code();
            $code->setController($arg);
            $code->getCode();
        }
    }
);

$di->setter['Aura\View\TwoStep']['addOuterPath'] = dirname(__DIR__) . '/src/Hari/Sample/Web/Greet/layouts';
$di->setter['Aura\View\TwoStep']['addInnerPath'] = dirname(__DIR__) . '/src/Hari/Sample/Web/User/partials';

$di->setter['Hari\Sample\Model\BaseModel']['setFilter'] = $di->lazyNew('Aura\Filter\RuleCollection');

$di->params['Hari\Sample\View\Helper\Pagination']['router'] = $di->lazyGet('router_map');
$di->params['Aura\View\HelperLocator']['registry']['pagination'] = $di->lazyNew('Hari\Sample\View\Helper\Pagination');

$di->params['Hari\Sample\Input\AntiCsrf']['session_manager'] = $di->lazyGet('session_manager');
$di->set('anti_csrf', $di->lazyNew('Hari\Sample\Input\AntiCsrf'));
$di->setter['Aura\Input\Form']['setAntiCsrf'] = $di->lazyGet('anti_csrf');

// Models and Form
$di->params['Hari\Framework\GenericFactory'] = [
    // a map of form names to form factories
    'map' => [
        'user.login' => $di->newFactory('Hari\Sample\Form\Login'),
        'user.register' => $di->newFactory('Hari\Sample\Form\Register'),
        'post.comment' => $di->newFactory('Hari\Sample\Form\Comment'),
        'hari.sample.postform' => $di->newFactory('Hari\Sample\Form\Post'),        
        'hari.contact.contactform' => $di->newFactory('Hari\Contact\Web\Forms\ContactForm'),
        'hari.sample.model.user' => $di->newFactory('Hari\Sample\Model\User'),
        'hari.sample.model.post' => $di->newFactory('Hari\Sample\Model\Post'),
        'hari.sample.model.comment' => $di->newFactory('Hari\Sample\Model\Comment'),
        'hari.sample.form.elements' => $di->newFactory('Hari\Sample\Form\FormElements'),
    ],
];

$di->set('form_factory', $di->lazyNew('Hari\Sample\Form\Factory'));
