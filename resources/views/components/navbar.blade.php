<nav id="main-nav">

    <div class="nav-parent">

        <div class="nav-logo">
            <img src="images/logo2.png" width="45px" height="45px"
                style="border-radius: 50%; transition: all 0.3s ease;" alt="">
            <h3 style="transition: all 0.3s ease;">KPMPCATS</h3>
        </div>

        <div class="nav-list">
            <ul class="tw:flex tw:gap-x-[4rem] m-0 p-0">
                <li class="tw:list-none">
                    <a href="{{ route("index") }}" class="tw:no-underline text-decoration-none">Home</a>
                </li>
                <li class="tw:list-none">
                    <a href="#section3" class="tw:no-underline text-decoration-none">About Us</a>
                </li>
                <li class="tw:list-none">
                    <a href="#how-it-works-parent" class="tw:no-underline text-decoration-none">How it Works</a>
                </li>
                <li class="tw:list-none">
                    <a href="#how-it-works-parent" class="tw:no-underline text-decoration-none">Loan Application</a>
                </li>
                <li class="tw:list-none">
                    <a href="#products_services" class="tw:no-underline text-decoration-none">Products & Services</a>
                </li>
            </ul>
        </div>

        <div class="nav-acc">
            <div class="nav-button">
                <a href="{{ route("LoginPage") }}" class="text-decoration-none fw-semibold">Get Started</a>
            </div>
        </div>

        <div class="nav-menu landingpage">
            <i class="fa fa-bars" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu1"
                aria-controls="staticBackdrop"></i>
        </div>
    </div>
</nav>

<!-- <style>
    #main-nav {
        background-color: var(--teal);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: background-color 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease, height 0.3s ease;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    #main-nav .nav-list a {
        color: rgba(255, 255, 255, 0.75);
        transition: color 0.4s ease;
    }

    #main-nav .nav-list ul li:nth-child(1) a {
        color: var(--blue) !important;
        font-weight: 600;
    }

    #main-nav .nav-logo h3 {
        color: #ffffff;
        transition: color 0.4s ease;
    }

    #main-nav .nav-menu .fa {
        color: #ffffff;
        transition: color 0.4s ease;
    }

    #main-nav.scrolled {
        background-color: #ffffff !important;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.10);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    #main-nav.scrolled .nav-list a {
        color: #1a2a5e !important;
        transition: color 0.4s ease;
    }

    #main-nav.scrolled .nav-list ul li:nth-child(1) a {
        color: var(--blue) !important;
        font-weight: 600;
    }

    #main-nav.scrolled .nav-logo h3 {
        color: #1a2a5e !important;
    }

    #main-nav.scrolled .nav-menu .fa {
        color: #1a2a5e !important;
    }

    #main-nav.scrolled .nav-acc .nav-button a {
        background-color: var(--blue);
        color: #ffffff;
    }

    #main-nav.scrolled .nav-acc .nav-button a:hover {
        background: var(--teal);
    }
</style>

<script>
    const nav = document.getElementById('main-nav');
    const navLogo = nav.querySelector('.nav-logo img');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
            navLogo.style.width = '45px';
            navLogo.style.height = '45px';
            nav.style.height = '64px';
        } else {
            nav.classList.remove('scrolled');
            navLogo.style.width = '45px';
            navLogo.style.height = '45px';
            nav.style.height = '64px';
        }
    });
</script> -->