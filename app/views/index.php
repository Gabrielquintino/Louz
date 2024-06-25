<!doctype html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iqon talk</title>
    <link rel="shortcut icon" type="image/png" href="../../resources/images/logos/iqon/favicon-16x16.png" />
    <link rel="stylesheet" href="../../resources/css/styles.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" />
    <link rel="stylesheet" href="../../resources/css/chat.css" />
    <link rel="stylesheet" href="../../resources/css/main.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="../../resources/libs/calendar/css/bootstrap-datetimepicker.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <style>
        .bootstrap-tagsinput .tag {
            background-color: #5bc0de;
            /* Cor de fundo da tag */
            border: 1px solid #5bc0de;
            /* Cor da borda da tag */
            color: white;
            /* Cor do texto da tag */
            padding: 2px 5px;
            /* Espaçamento interno da tag */
            border-radius: 4px;
            /* Bordas arredondadas */
        }
        .bootstrap-tagsinput {
            width: 100%;
        }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="./index" class="text-nowrap logo-img">
                        <img src="../../resources/images/logos/iqon/iqontalk-dark.png" width="80" alt="" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./index" aria-expanded="false">
                                <span>
                                    <i class="ti ti-layout-dashboard"></i>
                                </span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./chatbot" aria-expanded="false">
                                <span class="icon-item-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-message-chatbot">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" />
                                        <path d="M9.5 9h.01" />
                                        <path d="M14.5 9h.01" />
                                        <path d="M9.5 13a3.5 3.5 0 0 0 5 0" />
                                    </svg>
                                </span>
                                <span class="hide-menu">ChatBot</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./atendimento" aria-expanded="false">
                                <span class="icon-item-icon">
                                    <i class="ti ti-headset"></i>
                                </span>
                                <span class="hide-menu">Atendimentos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./crm" aria-expanded="false">
                                <span>
                                    <i class="ti ti-heart-handshake"></i>
                                </span>
                                <span class="hide-menu">CRM</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./produtos" aria-expanded="false">
                                <span>
                                    <i class="ti ti-package"></i>
                                </span>
                                <span class="hide-menu">Produtos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./eventos" aria-expanded="false">
                                <span>
                                    <i class="ti ti-ticket"></i>
                                </span>
                                <span class="hide-menu">Eventos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./vendas" aria-expanded="false">
                                <span>
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cash-register"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 15h-2.5c-.398 0 -.779 .158 -1.061 .439c-.281 .281 -.439 .663 -.439 1.061c0 .398 .158 .779 .439 1.061c.281 .281 .663 .439 1.061 .439h1c.398 0 .779 .158 1.061 .439c.281 .281 .439 .663 .439 1.061c0 .398 -.158 .779 -.439 1.061c-.281 .281 -.663 .439 -1.061 .439h-2.5" /><path d="M19 21v1m0 -8v1" /><path d="M13 21h-7c-.53 0 -1.039 -.211 -1.414 -.586c-.375 -.375 -.586 -.884 -.586 -1.414v-10c0 -.53 .211 -1.039 .586 -1.414c.375 -.375 .884 -.586 1.414 -.586h2m12 3.12v-1.12c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2" /><path d="M16 10v-6c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-4c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414v6m8 0h-8m8 0h1m-9 0h-1" /><path d="M8 14v.01" /><path d="M8 17v.01" /><path d="M12 13.99v.01" /><path d="M12 17v.01" /></svg>
                                </span>
                                <span class="hide-menu">Vendas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./agendamentos" aria-expanded="false">
                                <span>
                                    <i class="ti ti-calendar-event"></i>
                                </span>
                                <span class="hide-menu">Agendamentos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a id="linkConfiguracoes" class="sidebar-link" href="./configuracoes?page=integracao" aria-expanded="false">
                                <span class="icon-item-icon">
                                    <i class="ti ti-settings"></i>
                                </span>
                                <span class="hide-menu">Configurações</span>
                            </a>
                        </li>

                        <li class="sidebar-item dropdown">
                            <a class="sidebar-link dropdown-toggle" href="#" id="relatoriosDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-chart-infographic"></i>
                                <span class="hide-menu">Relatórios</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="relatoriosDropdown">
                                <li><a class="dropdown-item" href="#">Relatório 1</a></li>
                                <li><a class="dropdown-item" href="#">Relatório 2</a></li>
                                <li><a class="dropdown-item" href="#">Relatório 3</a></li>
                            </ul>
                        </li>
                    </ul>
                    <!-- <div class="unlimited-access hide-menu bg-light-primary position-relative mb-7 mt-5 rounded">
                        <div class="d-flex">
                            <div class="unlimited-access-title me-3">
                                <h6 class="fw-semibold fs-4 mb-6 text-dark w-85">Upgrade to pro</h6>
                                <a href="https://adminmart.com/product/modernize-bootstrap-5-admin-template/" target="_blank" class="btn btn-primary fs-2 fw-semibold lh-sm">Buy Pro</a>
                            </div>
                            <div class="unlimited-access-img">
                                <img src="../../resources/images/backgrounds/rocket.png" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div> -->
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>

        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                                <i class="ti ti-bell-ringing"></i>
                                <div class="notification bg-primary rounded-circle"></div>
                            </a>
                        </li>
                    </ul>
                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="me-3">Olá, <span id="SESSION_CODE"><?php echo $_SESSION['usuario_nome']; ?></span></span>
                                    <img src="../../resources/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                                    <div class="message-body">
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-user fs-6"></i>
                                            <p class="mb-0 fs-3">Meu perfil</p>
                                        </a>
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-mail fs-6"></i>
                                            <p class="mb-0 fs-3">Minha conta</p>
                                        </a>
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                            <i class="ti ti-list-check fs-6"></i>
                                            <p class="mb-0 fs-3">Minhas tarefas</p>
                                        </a>
                                        <a href="./authentication-login.html" class="btn btn-outline-primary mx-3 mt-2 d-block">Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>

            <?php include 'scripts.html'; ?>

            <div class="container-fluid">
                <?php
                // Inclui o arquivo especificado pela variável $html
                include $html;
                ?>
            </div>
        </div>
    </div>

    <script>
        var objMain = new Main();
        var objUtils = new Utils();
    </script>
</body>

</html>