<?php
namespace ViewComponents\ViewComponents\WebApp;

use ViewComponents\TestingHelpers\Application\Http\DefaultLayoutTrait;
use ViewComponents\ViewComponents\Component\CollectionView;
use ViewComponents\ViewComponents\Component\Compound;
use ViewComponents\ViewComponents\Component\Control\FilterControl;
use ViewComponents\ViewComponents\Component\Control\PageSizeSelectControl;
use ViewComponents\ViewComponents\Component\Control\SortingSelectControl;
use ViewComponents\ViewComponents\Component\DataView;
use ViewComponents\ViewComponents\Component\Control\PaginationControl;
use ViewComponents\ViewComponents\Component\ManagedList\RecordView;
use ViewComponents\ViewComponents\Component\Part;
use ViewComponents\ViewComponents\Component\TemplateView;
use ViewComponents\ViewComponents\Input\InputOption;
use ViewComponents\ViewComponents\Input\InputSource;
use ViewComponents\ViewComponents\Component\Container;
use ViewComponents\ViewComponents\Component\ManagedList;
use ViewComponents\ViewComponents\Component\Debug\SymfonyVarDump;
use ViewComponents\ViewComponents\Component\Html\Tag;
use ViewComponents\ViewComponents\Data\ArrayDataProvider;
use ViewComponents\ViewComponents\Data\DbTableDataProvider;
use ViewComponents\ViewComponents\Data\Operation\FilterOperation;
use ViewComponents\ViewComponents\Data\Operation\SortOperation;
use ViewComponents\ViewComponents\WebApp\Components\PersonView;
use ViewComponents\ViewComponents\Rendering\SimpleRenderer;
use ViewComponents\ViewComponents\Customization\Bootstrap\BootstrapStyling;
use ViewComponents\ViewComponents\Service\Services;

class Controller
{
    use DefaultLayoutTrait;

    protected function getUsersData()
    {
        return include(TESTING_HELPERS_DIR . '/resources/fixtures/users.php');
    }

    protected function getDataProvider($operations = [])
    {
        return (isset($_GET['use-db']) && $_GET['use-db'])
            ? new DbTableDataProvider(
                \ViewComponents\TestingHelpers\dbConnection(),
                'test_users',
                $operations
            )
            : new ArrayDataProvider(
                $this->getUsersData(),
                $operations
            );
    }

    public function index()
    {
        return $this->page('index', 'Index page');
    }

    public function demo0()
    {
        $this->layout()->addChild(new DataView("[I'm component attached directly to layout]"));
        return $this->page(null, 'Attaching components directly to layout');
    }

    /**
     * Basic usage of CollectionView component.
     *
     * @return string
     */
    public function demo1()
    {
        $view = new CollectionView($this->getUsersData(), [new PersonView]);
        return $this->page($view, 'Basic usage of CollectionView component');
    }

    /**
     * Demo1 extended by HtmlBuilder usage.
     *
     * @return string
     */
    public function demo2()
    {
        $html = Services::htmlBuilder();
        $data = $this->getUsersData();
        $view = new Container([
            $html->h1('Users List'),
            $html->hr(),
            new CollectionView($data, [new PersonView]),
            $html->hr(),
            $html->div('Footer')
        ]);
        return $this->page($view, 'HtmlBuilder');
    }

    /**
     * Array Data Provider with sorting.
     *
     * @return string
     */
    public function demo3()
    {
        $data = $this->getUsersData();

        $view = new Container([
            new DataView('<h1>Users List</h1>'),
            new CollectionView(
                new ArrayDataProvider(
                    $data,
                    [new SortOperation('name')]
                ),
                [new PersonView])
        ]);
        return $this->page($view, 'Array Data Provider with sorting');
    }

    /**
     * Filtering controls.
     *
     * @return string
     */
    public function demo4_1()
    {
        $provider = $this->getDataProvider([SortOperation::asc('name')]);

        $filter1 = new FilterControl(
            'name',
            FilterOperation::OPERATOR_EQ,
            new InputOption('name_filter', $_GET)
        );
        $filter2 = new FilterControl(
            'role',
            FilterOperation::OPERATOR_EQ,
            new InputOption('role_filter', $_GET)
        );

        $view = new Container([
            new DataView('<h1>Users List</h1>'),
            new Tag('form', [], [
                $filter1,
                $filter2,
                new Tag('button', ['type' => 'submit'], [
                    new DataView('Filter')
                ]),
            ]),
            new CollectionView(
                $provider,
                [new PersonView]
            )
        ]);
        $provider->operations()->add($filter1->getOperation());
        $provider->operations()->add($filter2->getOperation());
        return $this->page($view, 'Filtering controls');
    }


    /**
     * Filtering controls in managed list
     *
     * @return string
     */
    public function demo4_2()
    {
        $provider = $this->getDataProvider();
        $list = new ManagedList($provider, [
            new RecordView(new SymfonyVarDump()),
            new FilterControl(
                'name',
                FilterOperation::OPERATOR_EQ,
                new InputOption('name_filter', $_GET)
            ),
            new FilterControl(
                'role',
                FilterOperation::OPERATOR_EQ,
                new InputOption('role_filter', $_GET)
            ),
        ]);
        return $this->page($list, 'Filtering controls in managed list');
    }

    /**
     * Filtering controls in managed list + styling + pagination + InputSource
     *
     * @return string
     */
    public function demo4_3()
    {
        $provider = $this->getDataProvider();
        $input = new InputSource($_GET);
        $list = new ManagedList(
            $provider,
            [
                new RecordView(new SymfonyVarDump()),
                new FilterControl(
                    'name',
                    FilterOperation::OPERATOR_EQ,
                    $input('name_filter')
                ),
                new FilterControl(
                    'role',
                    FilterOperation::OPERATOR_EQ,
                    $input('role_filter')
                ),
                new SortingSelectControl(
                    [
                        null => 'None',
                        'id' => 'ID',
                        'name' => 'Name',
                        'role' => 'Role',
                        'birthday' => 'Birthday',
                    ],
                    $input('sort_field'),
                    $input('sort_dir')
                ),
                new PaginationControl($input('page', 1), 5),
                new PageSizeSelectControl($input('page_size', 5), [2, 5, 10]),
            ]
        );

        $styling = new BootstrapStyling();
        $styling->apply($list);

        return $this->page($list, 'Filtering controls in managed list + styling + pagination + InputSource');
    }

    /**
     * Hiding submit button automatically
     * @return string
     */
    public function demo4_4()
    {
        $provider = $this->getDataProvider();
        $input = new InputSource($_GET);
        $list = new ManagedList(
            $provider,
            [
                new RecordView(new SymfonyVarDump),
                new PaginationControl(
                    $input('page', 1),
                    10,
                    $provider
                )
            ]
        );
        $styling = new BootstrapStyling();
        $styling->apply($list, $list->getComponent('container'));
        return $this->page($list, 'Hiding submit button automatically');
    }

    /**
     * @return string
     */
    public function demo5()
    {
        $panel = new Tag('div', ['class' => 'panel panel-success']);
        $header = new Tag('div', ['class' => 'panel-heading']);
        $body = new Tag('div', ['class' => 'panel-body']);
        $footer = new Tag('div', ['class' => 'panel-footer']);
        $compound = new Compound([
            (new Part($panel))->setId('panel'),
            (new Part($header))->setId('header')->setDestinationParentId('panel'),
            (new Part($body))->setId('body')->setDestinationParentId('panel'),
            (new Part($footer))->setId('footer')->setDestinationParentId('panel'),
        ]);

        $header->addChild(new DataView('<b>Panel Header</b>'));
        $body->addChild(new DataView('Panel Body'));
        $footer->addChild(new DataView('Panel Footer'));

        $container = new Tag('div', ['class' => 'container'], [$compound]);
        $styling = new BootstrapStyling();
        $styling->apply($container);
        $compound->addChild(new DataView('Text added after footer'));

        return $this->page($compound, 'Usage of Compounds');
    }

    /**
     * Renderer
     *
     * @return string
     */
    public function demo6()
    {
        $renderer = new SimpleRenderer([
            __DIR__ . '/resources/views'
        ]);
        return $this->page(
            $renderer->render('demo/template1')
            . $renderer->render('demo/template_with_var', ['var' => 'ok']),
            'Renderer Usage'
        );

    }

    /**
     * Template view
     * @return string
     */
    public function demo7()
    {
        $renderer = new SimpleRenderer([
            __DIR__ . '/resources/views'
        ]);
        $view = new TemplateView('demo/template_view', [], $renderer);
        return $this->page($view, 'Template view');
    }
}
