<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Blogs</title>

    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/blogs.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- bootstrap link --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body style="background-color: #ECF6FF">

    <div class="container-fluid p-0 m-0">
        @include("navbar")
        @include("offcanvas")

        <main class="p-5 d-flex flex-xl-row flex-md-column flex-sm-column flex-sx-column flex-xs-column">

            <div data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="800" class="blogs-image"
                style="border-bottom-left-radius: 20px;">
                <img src="images/blogpicture4.jpg" class="max-w-full max-h-full tw:h-[100%] object-cover" alt="">
            </div>
            <div data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="1000"
                class="blogs-content tw:bg-[#3E8FFF]" style="border: 1px solid rgba(0,0,0,0.3);">
                <h4 class="py-2 px-3">Featured Post</h4>

                <p class="content" style="color: white">10 Smart Ways to Build Your Emergency Fund
                    Learn practical strategies for creating a financial safety net that will protect you and your family
                    during unexpected situations.</p>

                <div class="tw:flex gap-3 content">
                    <span style="color: white"><i class="fa fa-calendar"></i> December 15, 2024</span>
                    <span style="color: white">•</span>
                    <span style="color: white">5 min read</span>
                </div>

                <div class="content">
                    <a href="#" class="py-2 px-4 text-decoration-none" style="border-radius: 10px">Read More <i
                            class="fa fa-chevron-right"></i></a>
                </div>
            </div>
        </main>

        <section class="px-5 pb-5 relative">

            {{-- <div class="menu-nav mb-3">
                <button class="open"><i class="fa fa-chevron-right"></i></button>

                <button class="close"><i class="fa fa-chevron-left"></i></button>
            </div> --}}

            <div class="menu-sidebar">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                            aria-selected="true">All Posts</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-financial" type="button" role="tab" aria-controls="pills-profile"
                            aria-selected="false">Financial Tips</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-cooperativenews" type="button" role="tab"
                            aria-controls="pills-contact" aria-selected="false">Cooperative News</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-memberstories" type="button" role="tab" aria-controls="pills-contact"
                            aria-selected="false">Member Stories</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-fineducation" type="button" role="tab" aria-controls="pills-contact"
                            aria-selected="false">Financial Education</button>
                    </li>
                </ul>
            </div>

            <ul class="nav nav-pills overflow-hidden tw:bg-[#808080]" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                        aria-selected="true">All Posts</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-financial" type="button" role="tab" aria-controls="pills-profile"
                        aria-selected="false">Financial Tips</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-cooperativenews" type="button" role="tab" aria-controls="pills-contact"
                        aria-selected="false">Cooperative News</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-memberstories" type="button" role="tab" aria-controls="pills-contact"
                        aria-selected="false">Member Stories</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-fineducation" type="button" role="tab" aria-controls="pills-contact"
                        aria-selected="false">Financial Education</button>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab"
                    tabindex="0">

                    <div class="sub-card-parent">
                        <div class="card-parent">
                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class="tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/6.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/7.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/8.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/9.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="pills-financial" role="tabpanel" aria-labelledby="pills-profile-tab"
                    tabindex="1">

                    <div class="sub-card-parent">

                        <div class="card-parent">
                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class="tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/6.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/7.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="tab-pane fade" id="pills-cooperativenews" role="tabpanel"
                    aria-labelledby="pills-contact-tab" tabindex="2">

                    <div class="sub-card-parent">
                        <div class="card-parent">
                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class="tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/6.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/7.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/7.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">Understanding Interest Rates: A Comprehensive Guide</p>

                                    <p>Demystifying how interest rates work and what they mean for your savings and
                                        loans.
                                    </p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="pills-memberstories" role="tabpanel" aria-labelledby="pills-contact-tab"
                    tabindex="3">

                    <div class="tw:flex tw:justify-center tw:items-center">
                        <div class="card-parent">
                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class="tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/6.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class=" tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="pills-fineducation" role="tabpanel" aria-labelledby="pills-contact-tab"
                    tabindex="4">

                    <div class="sub-card-parent">
                        <div class="card-parent">
                            <div data-aos="fade-up" data-aos-duration="1000" class="card">
                                <div class="card-header">
                                    <img src="images/5.jpg" alt="">
                                </div>

                                <div class="card-body tw:flex tw:flex-col gap-2 p-4">
                                    <div class="tw:flex gap-3 content">
                                        <span><i class="fa fa-calendar"></i> December 15, 2024</span>
                                        <span>•</span>
                                        <span>5 min read</span>
                                    </div>

                                    <p class="">From Small Store to Growing Business: Anna's Success Story</p>

                                    <p>How one member used a business loan to transform her small sari-sari store into a
                                        thriving grocery business.</p>

                                    <div class="tw:flex tw:justify-between tw:items-center mt-3">
                                        <p style="margin: 0">By Admin Team</p>

                                        <a href="#" class="text-decoration-none" style="color: #155DFC">Read <i
                                                class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <footer class="p-5 pt-5">
            <div class="pt-5 tw:bg-[#3E8FFF] tw:flex tw:justify-center tw:items-center tw:flex-col gap-3 p-5"
                style="border-radius: 20px">
                <h4>Stay Updated</h4>

                <p class="mt-2 text-white">Subscribe to our newsletter to receive the latest blog posts, financial tips,
                    and
                    cooperative news.</p>

                <div class="tw:flex gap-3 mt-3 footer-subscribe">
                    <input class="form-control" type="text" name="" placeholder="Enter your email">
                    <button>Subscribe</button>
                </div>
            </div>
        </footer>
    </div>

    {{-- AOS animation link js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    {{-- Bootstrap link --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script nonce="{{ csp_nonce() }}">
        AOS.init();
    </script>
</body>

</html>