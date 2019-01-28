<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#2e8cc2">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="author" content="Marx JMoura">
    <meta name="copyright" content="LogiQ System">
    <meta name="description"
          content="Admin 4B is an open source and free admin template built on top of Bootstrap 4. Quickly customize with our Sass variables and mixins.">
    <title>Admin 4B · Bootstrap 4 Admin Template</title>
    <link rel="icon" href="/themes/test2/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    @yield('styles')
    @stack('styles')
    <link href="/themes/test2/dist/admin4b.min.css" rel="stylesheet">
    <link href="/themes/test2/assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="app">
    <div class="app-sidebar">
        <div class="sidebar-header">
            <svg class="avatar">
                <use href="/themes/test2/assets/img/faces.svg#john"/>
            </svg>
            <div class="username"><span>John Doe</span>
                <small>john_doe@email.com</small>
            </div>
        </div>
        <div id="sidebar-nav" class="sidebar-nav">
            <ul>
                <li><a href="/themes/test2/index.html"><span class="sidebar-nav-icon fa fa-rocket"></span> <span
                                class="sidebar-nav-text">Get started</span></a></li>
            </ul>
            <hr>
            <span class="sidebar-nav-header">Guideline</span>
            <ul>
                <li><a href="#device-controls" data-toggle="collapse"><span
                                class="sidebar-nav-icon fa fa-laptop"></span> <span class="sidebar-nav-text">Device controls</span></a>
                    <ul id="device-controls" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/device-controls/camera.html">Camera</a></li>
                        <li><a href="/themes/test2/pages/device-controls/file-manager.html">File manager</a></li>
                    </ul>
                </li>
                <li><a href="#forms" data-toggle="collapse"><span class="sidebar-nav-icon fa fa-pencil"></span> <span
                                class="sidebar-nav-text">Forms</span></a>
                    <ul id="forms" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/forms/basic-form.html">Basic form</a></li>
                        <li><a href="/themes/test2/pages/forms/tabbed-form.html">Tabbed form</a></li>
                    </ul>
                </li>
                <li><a href="#input-controls" data-toggle="collapse"><span
                                class="sidebar-nav-icon fa fa-pencil-square-o"></span> <span class="sidebar-nav-text">Input controls</span></a>
                    <ul id="input-controls" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/input-controls/checkbox.html">Checkbox</a></li>
                        <li><a href="/themes/test2/pages/input-controls/input-date.html">Input date</a></li>
                        <li><a href="/themes/test2/pages/input-controls/input-suggestion.html">Input suggestion</a></li>
                        <li><a href="/themes/test2/pages/input-controls/radio-button.html">Radio button</a></li>
                        <li><a href="/themes/test2/pages/input-controls/toggle-switch.html">Toggle switch</a></li>
                    </ul>
                </li>
                <li><a href="#layout" data-toggle="collapse"><span class="sidebar-nav-icon fa fa-clone"></span> <span
                                class="sidebar-nav-text">Layout</span></a>
                    <ul id="layout" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/layout/sidebar.html">Sidebar</a></li>
                        <li><a href="/themes/test2/pages/layout/spinner.html">Spinner</a></li>
                        <li><a href="/themes/test2/pages/layout/table.html">Table</a></li>
                    </ul>
                </li>
                <li><a href="#reference" data-toggle="collapse"><span class="sidebar-nav-icon fa fa-code"></span> <span
                                class="sidebar-nav-text">Reference</span></a>
                    <ul id="reference" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/reference/callout.html">Callout</a></li>
                        <li><a href="/themes/test2/pages/reference/code-highlight.html">Code highlight</a></li>
                    </ul>
                </li>
            </ul>
            <hr>
            <span class="sidebar-nav-header">Content</span>
            <ul>
                <li><a href="/themes/test2/pages/content/chat.html"><span
                                class="sidebar-nav-icon fa fa-comments"></span> <span
                                class="sidebar-nav-text">Chat</span></a></li>
                <li><a href="/themes/test2/pages/content/dashboard.html"><span
                                class="sidebar-nav-icon fa fa-pie-chart"></span> <span class="sidebar-nav-text">Dashboard</span></a>
                </li>
                <li><a href="#pages" data-toggle="collapse"><span class="sidebar-nav-icon fa fa-file"></span> <span
                                class="sidebar-nav-text">Pages</span></a>
                    <ul id="pages" class="collapse" data-parent="#sidebar-nav">
                        <li><a href="/themes/test2/pages/content/blank-page.html" class="sidebar-nav-link">Blank
                                page</a></li>
                        <li><a href="/themes/test2/pages/content/error-404.html" class="sidebar-nav-link">Error 404</a>
                        </li>
                        <li><a href="/themes/test2/pages/content/error-500.html" class="sidebar-nav-link">Error 500</a>
                        </li>
                    </ul>
                </li>
                <li><a href="/themes/test2/pages/content/settings.html"><span class="sidebar-nav-icon fa fa-cog"></span>
                        <span class="sidebar-nav-text">Settings</span></a></li>
                <li><a href="/themes/test2/pages/content/timeline.html"><span
                                class="sidebar-nav-icon fa fa-clock-o"></span> <span
                                class="sidebar-nav-text">Timeline</span></a></li>
            </ul>
            <hr>
            <ul>
                <li><a href="/themes/test2/pages/content/signin.html"><span
                                class="sidebar-nav-icon fa fa-power-off"></span> <span
                                class="sidebar-nav-text">Log out</span></a></li>
            </ul>
        </div>
    </div>
    <div class="app-content">
        <div class="content-header">
            <nav class="navbar navbar-expand navbar-light bg-white">
                <div class="navbar-brand">
                    <button type="button" class="btn btn-sidebar" data-toggle="sidebar"><i class="fa fa-bars"></i>
                    </button>
                    <span class="pr-2">Admin 4B</span>
                    <a href="https://github.com/logiqsystem/admin4b" class="text-dark decoration-none" data-toggle="tooltip" data-placement="right" title="Fork me on GitHub"><i class="fa fa-github"></i></a></div>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown"><a href="#" class="nav-link dropdown-toggle" role="button"
                                                     data-toggle="dropdown" aria-haspopup="true"
                                                     aria-expanded="false"><span class="badge badge-pill badge-primary">3</span>
                            <i class="fa fa-bell-o"></i></a>
                        <div class="dropdown-menu dropdown-menu-right"><a
                                    href="/themes/test2/pages/content/notification.html" class="dropdown-item">
                                <small class="dropdown-item-title">Lorem ipsum (today)</small>
                                <br>
                                <div>Lorem ipsum dolor sit amet...</div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/themes/test2/pages/content/notification.html" class="dropdown-item">
                                <small class="text-muted">Lorem ipsum (yesterday)</small>
                                <br>
                                <div>Lorem ipsum dolor sit amet...</div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/themes/test2/pages/content/notification.html" class="dropdown-item">
                                <small class="text-muted">Lorem ipsum (12/25/2017)</small>
                                <br>
                                <div>Lorem ipsum dolor sit amet...</div>
                            </a>
                            <div class="dropdown-divider">

                            </div>
                            <a href="/themes/test2/pages/content/notifications.html" class="dropdown-item dropdown-link">See all notifications</a>
                        </div>
                    </li>
                </ul>
            </nav>

            @yield('breadcrumb')
{{--            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Get started</li>
                </ol>
            </nav>--}}
        </div>

        <div class="content-body">
            <div class="container">

                @yield('advertTop')
                @yield('advertUser')
                {{--@yield('note')--}}
                @yield('flash')
                @yield('header')
                @yield('content')
                @yield('advertBottom')

                <p>
                    Admin 4B is an open source admin template built on top of <a href="https://getbootstrap.com/">Bootstrap
                        4</a>. The source code can be found on the <a href="https://github.com/logiqsystem/admin4b">GitHub
                        repo</a>.
                </p>
                <p>
                    <a href="https://github.com/logiqsystem/admin4b#quick-start" class="btn btn-outline-primary">Download
                        Admin 4B</a>
                </p>

                <h2>Quick start</h2>
                <p>
                    This template uses many bootstrap features, so you need to have some knowledge in this toolkit.
                    Before you continue, please, take a look at the <a href="https://getbootstrap.com/">bootstrap
                        documentation</a>.
                </p>

                <p>
                    The <code>admin4b.min.*</code> is a template bundle that already includes jQuery, Bootstrap, Popper
                    and Admin 4B theming and plugins.</p>
                <p>You can enrich the theme by using an icon library like <a href="http://fontawesome.io/">Font
                        Awesome</a>.
                </p>

                <h3>CSS</h3>
                <p>
                    Copy-paste the stylesheet <code>&lt;link&gt;</code> into your <code>&lt;head&gt;</code> after the
                    font stylesheets to load our CSS.
                </p>

                <div class="source-code">
                    <a href="#css-setup" data-toggle="collapse"><i class="fa fa-code"></i> Source code</a>

                    <div id="css-setup" class="collapse">
            <pre>
              <code class="html">&lt;!-- link to Font Awesome CSS --&gt;
&lt;link rel="stylesheet" href="admin4b.min.css"&gt;
              </code>
            </pre>
                    </div>
                </div>

                <h3>JS</h3>
                <p>
                    Place the following <code>&lt;script&gt;</code> tags near the end of your pages, right before the
                    closing <code>&lt;body&gt;</code> tag.
                </p>

                <div class="source-code">
                    <a href="#js-setup" data-toggle="collapse"><i class="fa fa-code"></i> Source code</a>
                    <div id="js-setup" class="collapse">
            <pre>
              <code class="html">
                &lt;script src="admin4b.min.js"&gt;&lt;/script&gt;
              </code>
            </pre>
                    </div>
                </div>

                <h2>Initial setup</h2>
                <p>An example code of the initial setup can be seen below.</p>
                <div class="source-code">
                    <a href="#initial-setup" data-toggle="collapse"><i class="fa fa-code"></i> Source code</a>
                    <div id="initial-setup" class="collapse">
            <pre>
              <code class="html">&lt;!doctype html&gt;
&lt;html lang="en"&gt;
  &lt;head&gt;
    &lt;meta charset="utf-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"&gt;
    &lt;!-- link to Font Awesome CSS --&gt;
    &lt;link rel="stylesheet" href="admin4b.min.css"&gt;
    &lt;title&gt;Admin 4B&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;script src="admin4b.min.js"&gt;&lt;/script&gt;
  &lt;/body&gt;
&lt;/html&gt;

              </code>
            </pre>
                    </div>
                </div>

                <div class="callout callout-info">
                    <h5>JavaScript initialization</h5>
                    <p>All components (including bootstrap) are automatically initialized by the template.</p></div>
                <h2>What's next?</h2>
                <p><a href="/themes/test2/pages/layout/sidebar.html">Configure the sidebar navigation</a></p></div>
        </div>
    </div>
</div>
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.bundle.min.js"></script>--}}

@yield('scripts')
@stack('scripts')
<script src="/themes/test2/dist/admin4b.min.js"></script>
<script src="/themes/test2/assets/js/themes/test2.js"></script>
</body>
</html>
