<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kingsland Pala Pala Cooperative</title>

    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/style.css?=5">
    <link rel="stylesheet" href="css_folder/loading.css?=6">

    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body style="background-color: #ffffff">

    <div class="container-fluid p-0 m-0">

        {{-- <div class="background-green"></div> --}}

        @include("components.navbar")
        @include("components.offcanvas")

        <button type="button" class="mv-back-arrow-up" id="backToTop" aria-label="Back to top">
            <svg class="progress-ring" viewBox="0 0 52 52" aria-hidden="true">
                <circle class="ring-bg" cx="26" cy="26" r="23"></circle>
                <circle class="ring-bar" cx="26" cy="26" r="23"></circle>
            </svg>
            <i class="fa fa-chevron-up"></i>
        </button>

        <main class="z-1">

            <!-- <div class="main-text">
                <h4>Personal loans from</h4>

                <h2>Top Lenders All </h2>
            </div> -->

            <!-- <section class="carousel" id="carousel" style="margin-top: 64px;">
                <div class="carousel-track" id="carouselTrack">

                    <div class="slide active">
                        <img class="slide-bg" src="../images/hero-3.png" alt="Trusted Cooperative Partner" />
                        <div class="slide-overlay"></div>
                        <div class="slide-chevron"></div>
                        <div class="slide-content">
                            <div class="slide-eyebrow">Loan Services</div>
                            <h2 class="slide-title">Your Loan <em>Approved</em> Instantly.</h2>
                            <div class="slide-rule"></div>
                            <p class="slide-body">We make loan applications simple, fast, and stress-free — so you can
                                focus on what matters most. Powered by cutting-edge cooperative management technology.
                            </p>

                            <div class="slide-btn-group">
                                <a href="#" class="slide-btn">Learn More &nbsp;→</a>

                                <a href="#" class="slide-btn">About Us &nbsp;→</a>
                            </div>
                        </div>
                    </div>

                    <div class="slide">
                        <img class="slide-bg" src="../images/hero-4.png" alt="Services at Your Fingertips" />
                        <div class="slide-overlay"></div>
                        <div class="slide-chevron"></div>
                        <div class="slide-content">
                            <div class="slide-eyebrow">Member Management</div>
                            <h2 class="slide-title">Know Every <em>Member</em> Every Details.</h2>
                            <div class="slide-rule"></div>
                            <p class="slide-body">A complete member registry with share capital tracking, contribution
                                history, tier management, and automated lifecycle notifications — all in one place.</p>

                            <div class="slide-btn-group">
                                <a href="#" class="slide-btn">Learn More &nbsp;→</a>

                                <a href="#" class="slide-btn">About Us &nbsp;→</a>
                            </div>
                        </div>
                    </div>

                    <div class="slide">
                        <img class="slide-bg" src="../images/hero-5.png" alt="Your Loan, Approved!" />
                        <div class="slide-overlay"></div>
                        <div class="slide-chevron"></div>
                        <div class="slide-content">
                            <div class="slide-eyebrow">Financial Reports</div>
                            <h2 class="slide-title">Reports that <em>Tell the</em> Full Story.</h2>
                            <div class="slide-rule"></div>
                            <p class="slide-body">We make loan applications simple, fast, and stress-free — so you can
                                focus on what matters most.</p>

                            <div class="slide-btn-group">
                                <a href="#" class="slide-btn">Learn More &nbsp;→</a>

                                <a href="#" class="slide-btn">About Us &nbsp;→</a>
                            </div>
                        </div>
                    </div>

                    <div class="slide">
                        <img class="slide-bg" src="../images/hero-6.png" alt="Growing Together as One" />
                        <div class="slide-overlay"></div>
                        <div class="slide-chevron"></div>
                        <div class="slide-content">
                            <div class="slide-eyebrow">Compliance & Governance</div>
                            <h2 class="slide-title">Stay <em>Compliant</em> Stay Trusted.</h2>
                            <div class="slide-rule"></div>
                            <p class="slide-body">Built-in CDA compliance tools, tamper-proof audit trails, and digital
                                member governance features ensure your cooperative operates with full transparency and
                                accountability.</p>

                            <div class="slide-btn-group">
                                <a href="#" class="slide-btn">Learn More &nbsp;→</a>

                                <a href="#" class="slide-btn">About Us &nbsp;→</a>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="carousel-prev" id="prevBtn">
                    <span>&#8592;</span>
                </button>
                <button class="carousel-next" id="nextBtn">
                    <span>&#8594;</span>
                </button>

                <div class="carousel-dots" id="carouselDots">
                    <button class="dot active"></button>
                    <button class="dot"></button>
                    <button class="dot"></button>
                    <button class="dot"></button>
                </div>

                <div class="carousel-counter"><span id="slideNum">01</span> / 04</div>

                <div class="carousel-progress" id="progress"></div>

                <div class="main-text">
                    <div class="first-slide">
                        <div class="slide-eye">Loan Services</div>
                        <h2>Your Loan <em>Approved</em> Instantly.</h2>
                        <p>We make loan applications simple, fast, and stress-free — so you can
                            focus on what matters most. Powered by cutting-edge cooperative management technology.</p>
                    </div>

                    <div class="second-slide">
                        <div class="slide-eye">Member Management</div>
                        <h2>Know Every <em>Member</em> Every Details.</h2>
                        <p>A complete member registry with share capital tracking, contribution
                            history, tier management, and automated lifecycle notifications — all in one place.</p>
                    </div>

                    <div class="third-slide">
                        <div class="slide-eye">Financial Reports</div>
                        <h2>Reports that <em>Tell the</em> Full Story.</h2>
                        <p>We make loan applications simple, fast, and stress-free — so you can
                            focus on what matters most.</p>
                    </div>

                    <div class="fourth-slide">
                        <div class="slide-eye">FCompliance & Governance</div>
                        <h2>Stay <em>Compliant</em> Stay Trusted.</h2>
                        <p>Built-in CDA compliance tools, tamper-proof audit trails, and digital
                            member governance features ensure your cooperative operates with full transparency and
                            accountability.</p>
                    </div>
                </div>

                <div class="main-img">
                    <img src="../images/hero-3.png" alt="">
                    <img src="../images/hero-4.png" alt="">
                    <img src="../images/hero-5.png" alt="">
                    <img src="../images/hero-6.png" alt="">
                </div>
            </section> -->


            <section class="carousel" id="carousel">
                <div class="carousel-track">
                    <div class="main-text">
                        <div class="slide first-slide active">
                            <div class="slide-eye">Loan Services</div>
                            <h2>Your Loan <em>Approved</em> Instantly.</h2>
                            <p>We make loan applications simple, fast, and stress-free — so you can
                                focus on what matters most. Powered by cutting-edge cooperative management technology.
                            </p>

                            <div class="button-group">
                                <a href="{{ route("LoginPage") }}">
                                    Apply Now

                                    <i class="fa fa-arrow-right"></i>
                                </a>

                                <a href="#how-it-works-parent">
                                    See How it Works

                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="member-parent">
                                <div class="profile-parent">
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                </div>
                                <div class="profile-text">
                                    <p><b>{{ number_format($loansApprovedThisYear) }}</b> loans approved this year</p>
                                </div>
                            </div>
                        </div>

                        <div class="slide second-slide">
                            <div class="slide-eye">Member Management</div>
                            <h2>Know Every <em>Member</em> Every Details.</h2>
                            <p>A complete member registry with share capital tracking, contribution
                                history, tier management, and automated lifecycle notifications — all in one place.</p>

                            <div class="button-group">
                                <a href="#">
                                    Apply Now

                                    <i class="fa fa-arrow-right"></i>
                                </a>

                                <a href="#">
                                    See How it Works

                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="member-parent">
                                <div class="profile-parent">
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                </div>
                                <div class="profile-text">
                                    <p><b>{{ number_format($loansApprovedThisYear) }}</b> loans approved this year</p>
                                </div>
                            </div>
                        </div>

                        <div class="slide third-slide">
                            <div class="slide-eye">Financial Reports</div>
                            <h2>Reports that <em>Tell the</em> Full Story.</h2>
                            <p>We make loan applications simple, fast, and stress-free — so you can
                                focus on what matters most.</p>

                            <div class="button-group">
                                <a href="#">
                                    Apply Now

                                    <i class="fa fa-arrow-right"></i>
                                </a>

                                <a href="#">
                                    See How it Works

                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="member-parent">
                                <div class="profile-parent">
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                </div>
                                <div class="profile-text">
                                    <p><b>{{ number_format($loansApprovedThisYear) }}</b> loans approved this year</p>
                                </div>
                            </div>
                        </div>

                        <div class="slide fourth-slide">
                            <div class="slide-eye">Compliance & Governance</div>
                            <!-- <h2>Trusted <em>Financing</em> for Your Future.</h2> -->
                            <h2>Making Loans <em>Simple</em> and Accessible.</h2>
                            <p>Built-in CDA compliance tools, tamper-proof audit trails, and digital
                                member governance features ensure your cooperative operates with full transparency.</p>

                            <div class="button-group">
                                <a href="#">
                                    Apply Now

                                    <i class="fa fa-arrow-right"></i>
                                </a>

                                <a href="#">
                                    See How it Works

                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="member-parent">
                                <div class="profile-parent">
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                    <div class="profile"></div>
                                </div>
                                <div class="profile-text">
                                    <p><b>{{ number_format($loansApprovedThisYear) }}</b> loans approved this year</p>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-dots">
                            <span class="dot active" data-slide="0"></span>
                            <span class="dot" data-slide="1"></span>
                            <span class="dot" data-slide="2"></span>
                            <span class="dot" data-slide="3"></span>
                        </div>
                    </div>

                    <div class="main-img">
                        <img src="../images/hero-3.png" class="slide-img active" alt="">
                        <img src="../images/hero-4.png" class="slide-img" alt="">
                        <img src="../images/hero-5.png" class="slide-img" alt="">
                        <img src="../images/hero-6.png" class="slide-img" alt="">

                        <div class="badge-parent badge-1">
                            <div class="badge-icon">
                                <i class="fa fa-tag"></i>
                            </div>
                            <div class="badge-text">
                                <span>Loan Offer</span>
                                <small>Pre-approved rate</small>
                            </div>
                        </div>
                        <div class="badge-parent badge-2">
                            <div class="badge-icon">
                                <i class="fa fa-check"></i>
                            </div>
                            <div class="badge-text">
                                <span>Approved</span>
                                <small>Funds released</small>
                            </div>
                        </div>
                        <div class="badge-parent badge-3">
                            <div class="badge-icon">
                                <i class="fa fa-chart-simple"></i>
                            </div>
                            <div class="badge-text">
                                <span>Savings</span>
                                <small>Manage and grow</small>
                            </div>
                        </div>
                        <div class="badge-parent badge-4">
                            <div class="badge-icon">
                                <i class="fa fa-calendar-days"></i>
                            </div>
                            <div class="badge-text">
                                <span>Repayment</span>
                                <small>Auto-scheduled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            {{-- <div class="main-text">
                <span>Established 2010 · 1,200 - Members</span>

                <h2>Built on <b>Trust,</b> Growing <b>Together</b></h2>

                <p>Members Cooperative empowers members through shared savings, affordable loans, and community-driven
                    livelihood programs.</p>

                <div class="main-buttons">
                    <a href="{{ route(" RegisterPage") }}">Become a Member <i class="fa fa-arrow-right"></i></a>

                    <a href="#">Learn More</a>
                </div>

                <div class="experience">
                    <div class="total-experience">
                        <h4>₱48M</h4>
                        <span>Total Capital Share</span>
                    </div>

                    <div class="total-experience">
                        <h4>1,240</h4>
                        <span>Active Members</span>
                    </div>

                    <div class="total-experience">
                        <h4>15+</h4>
                        <span>Years of Service</span>
                    </div>
                </div>
            </div>
            <div class="main-image">
                <div class="overlay"></div>
                <div class="image-wrapper wrapper1">
                    <img class="image1" src="images/coop1.jpg" alt="">
                </div>

                <div class="image-wrapper wrapper2">
                    <img class="image2" src="images/coop2.jpg" alt="">
                </div>

                <div class="image-wrapper wrapper3">
                    <img class="image3" src="images/coop3.jpg" alt="">
                </div>
            </div> --}}
        </main>

        <!-- <div class="marquee-sub-parent">
            <div class="marquee-parent">
                <div class="marquee-section-parent">
                    <div class="stat">
                        <strong>₱2.4B+</strong>
                        <span>Funded to Members</span>
                    </div>

                    <div class="stat">
                        <strong>12,400</strong>
                        <span>Active Members</span>
                    </div>

                    <div class="stat">
                        <strong>48 Hrs</strong>
                        <span>Avg. Approval Time</span>
                    </div>

                    <div class="stat">
                        <strong>4.9 / 5</strong>
                        <span>Average Rating</span>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- <div class="marquee-bar">
            <div class="marquee-track">
                <span class="marquee-item">Member Governance <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Loan & Savings Management <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Financial Reporting <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Digital Voting <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Dividend Computation <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Compliance Tools <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Member Portal <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Audit Trails <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Member Governance <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Loan & Savings Management <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Financial Reporting <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Digital Voting <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Dividend Computation <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Compliance Tools <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Member Portal <span class="marquee-sep">✦</span></span>
                <span class="marquee-item">Audit Trails <span class="marquee-sep">✦</span></span>
            </div>
        </div> -->

        <section id="section3">
            <div class="section3-parent" id="section3-parent">
                <div class="section-image reveal reveal-delay-1">
                    <!-- <img src="images/benefits.jpg" alt=""> -->
                    <img src="images/about.jpg" alt="">

                    <div class="how-many-years">
                        <div class="years-icon">
                            <i class="fa fa-chart-simple"></i>
                        </div>
                        <div class="years-text">
                            <p>34 yrs</p>
                            <span>Serving members & drivers</span>
                        </div>
                    </div>
                </div>
                <div class="section-text reveal reveal-delay-2">
                    <div class="header">
                        <div class="header-tag">About us</div>
                    </div>
                    <!-- <h2 class="text-left">Rooted in Community, <b>Driven by Trust.</b></h2> -->
                    <h2 class="text-left">Empowering Members. <b>Building Stronger</b> Communities.</h2>

                    <div class="">
                        <p class="text-left">For years, KPMPCATS has been committed to helping its members achieve
                            financial
                            stability through cooperative values, responsible lending, and community-focused services.
                            As a
                            trusted multipurpose cooperative, we work together to create opportunities, promote growth,
                            and
                            improve the lives of every member.
                        </p>
                    </div>

                    <div style="margin-top: 20px">
                        <div class="card-box">
                            <div class="card-box-body tw:p-[8px]">
                                <div class="card-box-header">
                                    <h3>Members Benefits</h3>

                                    <p class="mw-100">As a cooperative member, you enjoy exclusive
                                        benefits and privileges designed to support
                                        your financial journey.</p>
                                </div>
                            </div>

                            <div class="benefits-group">

                                <div class="benefit-1">
                                    <div class="benefit">
                                        <div class="icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <span>Affordable and member-friendly loan services</span>
                                    </div>

                                    <div class="benefit">
                                        <div class="icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <span>Savings and financial assistance programs</span>
                                    </div>

                                    <div class="benefit">
                                        <div class="icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <span>Transparent and accountable cooperative management</span>
                                    </div>
                                </div>

                                <div class="benefit-2">
                                    <div class="benefit">
                                        <div class="icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <span>Community-centered initiatives and member support</span>
                                    </div>

                                    <div class="benefit">
                                        <div class="icon">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <span>A trusted partner for long-term financial growth</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="how-it-works-parent" id="how-it-works-parent">
            <div class="how-it-works">
                <div class="how-it-works-header">
                    <div class="how-it-card-tag">How it Works</div>

                    <h2>How to <b>Simulate</b> Loans with KPMPCATS?</h2>
                </div>
                <div class="how-it-works-body">
                    <div class="how-it-line"></div>
                    <div class="work-card">
                        <div class="word-card-icon">
                            <img src="images/choose-product.jpg" alt="">
                        </div>
                        <h3>Choose a Loan Product</h3>
                        <p>Select the loan that best fits your financial needs and review its terms and repayment
                            options. Compare interest rates and eligibility requirements carefully. Apply with
                            confidence.</p>
                    </div>

                    <div class="work-card">
                        <div class="word-card-icon">
                            <img src="images/submit.jpg" alt="">
                        </div>
                        <h3>Submit Your Application</h3>
                        <p>Complete the application form and submit the required documents for review. Ensure all
                            details provided are accurate and up to date. Our team will verify your submission promptly.
                        </p>
                    </div>

                    <div class="work-card">
                        <div class="word-card-icon">
                            <img src="images/receive.jpg" alt="">
                        </div>
                        <h3>Receive Approval & Funds</h3>
                        <p>After approval, your loan will be processed without delay. Funds will be released through
                            your chosen disbursement method, following the cooperative's established loan policies.</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- <section class="" id="section1">

            <div class="parent-tutorial">
                <div class="card-side">
                    <div class="card-tag">
                        What We Offer
                    </div>
                    <h2 class="text-left mt-3">Get Your Loan In <b>4 Easy Steps</b></h2>

                    <p class="text-left">We've simplified the loan process so you spend less time on paperwork and more
                        time
                        on what matters.</p>

                    <div class="step-to-apply">
                        <div class="step-card">
                            <div class="number">
                                <h5>01</h5>
                            </div>
                            <div class="words">
                                <p>Create Your Account</p>
                                <span>Sign up as a member. Membership is open to all Filipinos aged 18 and above with a
                                    valid ID.</span>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="number">
                                <h5>02</h5>
                            </div>
                            <div class="words">
                                <p>Choose a Loan Product</p>
                                <span>Pick the loan type that fits your need and check your estimated monthly
                                    amortization.</span>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="number">
                                <h5>03</h5>
                            </div>
                            <div class="words">
                                <p>Submit Requirements</p>
                                <span>Upload your documents online — valid ID, proof of income, and completed
                                    application
                                    form.</span>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="number">
                                <h5>04</h5>
                            </div>
                            <div class="words">
                                <p>Receive Your Funds</p>
                                <span>Upon approval, funds are released within 1–3 business days directly to your
                                    account.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="how-visual">
                    <video autoplay muted loop playsinline>
                        <source src="videos/mobile-steps.mp4" type="video/mp4">
                    </video>
                    <div class="phone-frame">
                    <div class="phone-notch">

                    </div>
                    <div class="app-screen">
                        <div class="app-logo">
                            <img src="images/logo2.png" alt="Logo">
                            <h4>KPMPCATS</h4>
                        </div>
                        <div class="app-header">
                            <div>
                                <span>Good morning 👋</span>
                                <h6>Juan Dela Cruz</h6>
                            </div>
                            <div class="dot-green">
                                
                            </div>
                        </div>

                        <div class="app-body">
                            <div class="app-balance-label">Total Loan Balance</div>
                            <div class="app-balance">₱142,500</div>
                            <div class="app-change">↑ 62% paid off</div>

                            <div class="app-divider"></div>

                            <div class="app-row">
                                <div class="app-row-label">Monthly Due</div>
                                <div class="app-row-val">₱4,250</div>
                            </div>

                            <div class="app-row">
                                <div class="app-row-label">Due Date</div>
                                <div class="app-row-val">June 15</div>
                            </div>

                            <div class="app-row">
                                <div class="app-row-label">Interest Rate</div>
                                <div class="app-row-val">4.8% p.a</div>
                            </div>

                            <div class="app-row">
                                <div class="app-row-label">Monthly Term</div>
                                <div class="app-row-val">6 months</div>
                            </div>
                        </div>

                        <div class="app-progress-bar">
                            <div class="app-progress-fill"></div>
                        </div>
                    </div>
                </div>
                </div>
            </div> -->

        <!-- <div class="card-box-parent">
                <div class="card-box reveal reveal-delay-1">
                    <div class="card-icon">
                        <p>01</p>
                    </div>
                    <div class="card-description">
                        <p>Digital Reporting</p>
                        <span>Automated financial reports and real-time dashboards, always accurate and on time.</span>
                    </div>
                </div>

                <div class="card-box reveal reveal-delay-2">
                    <div class="card-icon">
                        <p>02</p>
                    </div>
                    <div class="card-description">
                        <p>Loan Management</p>
                        <span>Automated financial reports and real-time dashboards, always accurate and on time.</span>
                    </div>
                </div>

                <div class="card-box reveal reveal-delay-1">
                    <div class="card-icon">
                        <p>03</p>
                    </div>
                    <div class="card-description">
                        <p>Member Portal</p>
                        <span>Automated financial reports and real-time dashboards, always accurate and on time.</span>
                    </div>
                </div>

                <div class="card-box reveal reveal-delay-2">
                    <div class="card-icon">
                        <p>04</p>
                    </div>
                    <div class="card-description">
                        <p>Secure Savings</p>
                        <span>Automated financial reports and real-time dashboards, always accurate and on time.</span>
                    </div>
                </div>

                <div class="card-box reveal reveal-delay-1">
                    <div class="card-icon">
                        <p>05</p>
                    </div>
                    <div class="card-description">
                        <p>Secure Savings</p>
                        <span>Automated financial reports and real-time dashboards, always accurate and on time.</span>
                    </div>
                </div>
            </div> -->

        {{-- <div class="d-flex justify-content-center align-items-center column-gap-5 flex-wrap">

            <div class="card reveal reveal-delay-1 tw:p-[1rem] tw:mt-[2rem]" style="border-radius: 20px">
                <div class="card-body tw:pb-[1.5rem]">

                    <div class="card-icon d-flex justify-content-center align-items-center p-0 border-0"
                        style="border-radius: 20px;">
                        <i class="fa fa-users"></i>
                    </div>

                    <h3 class="mt-4" style="font-size: 18px">Community Driven</h3>

                    <p class="tw:w-[250px] tw:text-base/8  tw:text-[14.5px] mt-3">Join a community of members
                        working together towards financial prosperity and mutual support.
                    </p>
                </div>
            </div>

            <div class="card reveal reveal-delay-2 tw:p-[1rem] tw:mt-[2rem]" style="border-radius: 20px">
                <div class="card-body tw:pb-[1.5rem]">

                    <div class="card-icon d-flex justify-content-center align-items-center p-0 border-0"
                        style="border-radius: 20px;">
                        <i class="fa fa-chart-line"></i>
                    </div>

                    <h3 class="mt-4" style="font-size: 18px">Flexible Loans</h3>

                    <p class="tw:w-[250px] tw:text-base/8  tw:text-[14.5px] mt-3">Access various loan types with
                        competitive rates designed to meet your personal and business
                        needs.
                    </p>
                </div>
            </div>

            <div class="card reveal reveal-delay-1 tw:p-[1rem] tw:mt-[2rem]" style="border-radius: 20px">
                <div class="card-body tw:pb-[1.5rem]">

                    <div class="card-icon d-flex justify-content-center align-items-center p-0 border-0"
                        style="border-radius: 20px;">
                        <i class="fa fa-shield"></i>
                    </div>

                    <h3 class="mt-4" style="font-size: 18px">Secure Savings</h3>

                    <p class="tw:w-[250px] tw:text-base/8  tw:text-[14.5px] mt-3">Build your savings with confidence
                        through our secure and transparent cooperative system.
                    </p>
                </div>
            </div>
        </div> --}}
        </section>

        <section class="" id="products_services">

            <div class="product_services_parent">
                <div class="header">
                    <!-- <span>Loan Products</span> -->
                    <div class="products-tag">Loan Products</div>

                    <h2 class="text-left reveal reveal-delay-1">Choose a Loan Built
                        for <b>Your Needs</b></h2>

                    <p class="text-left tw:text-[16px] reveal reveal-delay-2">Choose from our
                        variety of loan
                        products
                        designed to meet your financial needs.
                    </p>
                </div>

                <div class="card-parent">

                    <div class="card reveal reveal-delay-1">
                        <div class="popular">
                            <div class="popular-text">Most Popular</div>
                        </div>
                        <div class="card-icon">
                            <i class="fa fa-user"></i>
                        </div>

                        <span>Loan</span>

                        <h4>Personal Loan</h4>

                        <p>For personal expenses, life events, home improvements, and everything in between.</p>

                        <hr>

                        <div class="starting">
                            <p>Starting at</p>
                            <p><b>2%</b></p>
                            <p>/ monthly</p>
                        </div>

                        <div class="check-parent">
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Quick approval processs</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Flexible repayment terms</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>No hidden charges</p>
                            </div>
                        </div>

                        <div class="apply">
                            <a href="{{ route("LoginPage") }}">
                                <p>Apply Now</p>
                                <!-- <p>&nbsp;→</p> -->
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        </div>
                    </div>

                    <div class="card reveal reveal-delay-2">
                        <div class="card-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>

                        <span>Loan</span>

                        <h4>Business Loan</h4>

                        <p>For personal expenses, life events, home improvements, and everything in between.</p>

                        <hr>

                        <div class="starting">
                            <p>Starting at</p>
                            <p><b>2%</b></p>
                            <p>/ monthly</p>
                        </div>

                        <div class="check-parent">
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Quick approval processs</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Flexible repayment terms</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>No hidden charges</p>
                            </div>
                        </div>

                        <div class="apply">
                            <a href="{{ route("LoginPage") }}">
                                <p>Apply Now</p>
                                <!-- <p>&nbsp;→</p> -->
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        </div>
                    </div>

                    <div class="card reveal reveal-delay-1">
                        <div class="card-icon">
                            <i class="fa fa-hand-holding-medical"></i>
                        </div>

                        <span>Loan</span>

                        <h4>Emergency Loan</h4>

                        <p>For personal expenses, life events, home improvements, and everything in between.</p>

                        <hr>

                        <div class="starting">
                            <p>Starting at</p>
                            <p><b>2%</b></p>
                            <p>/ monthly</p>
                        </div>

                        <div class="check-parent">
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Quick approval processs</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Flexible repayment terms</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>No hidden charges</p>
                            </div>
                        </div>

                        <div class="apply">
                            <a href="{{ route("LoginPage") }}">
                                <p>Apply Now</p>
                                <!-- <p>&nbsp;→</p> -->
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        </div>
                    </div>

                    <div class="card reveal reveal-delay-2">
                        <div class="card-icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div>

                        <span>Loan</span>

                        <h4>Education Loan</h4>

                        <p>For personal expenses, life events, home improvements, and everything in between.</p>

                        <hr>

                        <div class="starting">
                            <p>Starting at</p>
                            <p><b>2%</b></p>
                            <p>/ monthly</p>
                        </div>

                        <div class="check-parent">
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Quick approval processs</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>Flexible repayment terms</p>
                            </div>
                            <div class="check-text">
                                <div class="check-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <p>No hidden charges</p>
                            </div>
                        </div>

                        <div class="apply">
                            <a href="{{ route("LoginPage") }}">
                                <p>Apply Now</p>
                                <!-- <p>&nbsp;→</p> -->
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        </div>
                    </div>
                    {{-- <div class="card reveal reveal-delay-1 mw-100" style="border-radius: 10px;">
                        <div class="card-body tw:flex tw:justify-start tw:flex-col">
                            <div class="card-header border-0 p-0">
                                <h3>Personal Loan</h3>

                                <p>For Personal expenses and life events.</p>
                            </div>

                            <hr style="margin: 10px 0px">

                            <span>Starting at 2%</span>
                        </div>
                    </div>

                    <div class="card reveal reveal-delay-2 mw-100" style="border-radius: 10px;">
                        <div class="card-body tw:flex tw:justify-start tw:flex-col">
                            <div class="card-header border-0 p-0">
                                <h3>Business Loan</h3>

                                <p>Grow your business with capital support</p>
                            </div>

                            <hr style="margin: 10px 0px">

                            <span>Starting at 2%</span>
                        </div>
                    </div>

                    <div class="card reveal reveal-delay-1 mw-100" style="border-radius: 10px;">
                        <div class="card-body tw:flex tw:justify-start tw:flex-col">
                            <div class="card-header border-0 p-0">
                                <h3>Emergency Loan</h3>

                                <p>Quick access for urgent financial needs.</p>
                            </div>

                            <hr style="margin: 10px 0px">

                            <span>Starting at 2%</span>
                        </div>
                    </div>

                    <div class="card reveal reveal-delay-2 mw-100" style="border-radius: 10px;">
                        <div class="card-body tw:flex tw:justify-start tw:flex-col">
                            <div class="card-header border-0 p-0">
                                <h3>Education Loan</h3>

                                <p>Make your dream home a reality.</p>
                            </div>

                            <hr style="margin: 10px 0px">

                            <span>Starting at 2%</span>
                        </div>
                    </div> --}}
                </div>
            </div>
        </section>

        <section id="section6">
            <div class="section6-parent">
                <div class="header">
                    <!-- <span>What We Offer</span> -->
                    <div class="section6-tag">What We Offer</div>

                    <h2>Everything a Cooperative <b>Needs to Thrive</b></h2>
                </div>

                <!-- <h3>Needs to Thrive</h3> -->

                <div class="card-parent-box">
                    <div class="card-box reveal reveal-delay-1">
                        <span>01</span>

                        <i class="fa fa-users"></i>

                        <h4>Member Management</h4>

                        <p>Comprehensive member registry with share capital tracking, membership tiers, contribution
                            history, and automated status notifications for every member lifecycle event.</p>
                    </div>

                    <div class="card-box reveal reveal-delay-2">
                        <span>02</span>

                        <i class="fa fa-users"></i>

                        <h4>Loan & Savings</h4>

                        <p>Comprehensive member registry with share capital tracking, membership tiers, contribution
                            history, and automated status notifications for every member lifecycle event.</p>
                    </div>

                    <div class="card-box reveal reveal-delay-1">
                        <span>03</span>

                        <i class="fa fa-users"></i>

                        <h4>Financial Reports</h4>

                        <p>Comprehensive member registry with share capital tracking, membership tiers, contribution
                            history, and automated status notifications for every member lifecycle event.</p>
                    </div>

                    <div class="card-box reveal reveal-delay-2">
                        <span>04</span>

                        <i class="fa fa-shield-halved"></i>

                        <h4>Compliance & Governance</h4>

                        <p>Built-in CDA compliance tools, tamper-proof audit trails, and digital member governance
                            features to keep the cooperative transparent and accountable.</p>
                    </div>

                    <div class="card-box reveal reveal-delay-1">
                        <span>05</span>

                        <i class="fa fa-coins"></i>

                        <h4>Dividends & Patronage</h4>

                        <p>Automatic computation of annual dividends and patronage refunds based on share capital and
                            member activity, credited straight to your savings account.</p>
                    </div>

                    <div class="card-box reveal reveal-delay-2">
                        <span>06</span>

                        <i class="fa fa-bell"></i>

                        <h4>Member Portal & Alerts</h4>

                        <p>A dedicated member portal with real-time notifications, announcements, polls, and seminar
                            tracking — everything you need to stay engaged and informed.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="section4">

            <div class="section4-parent">
                <div class="header">
                    <!-- <span>Member Stories</span> -->
                    <div class="section4-tag">Member Stories</div>
                    <h2 class="text-left">What Our <b>Members Say</b></h2>
                    <p class="text-left">Real experiences from the people who trust us with their financial future.</p>
                </div>

                <div class="card-box-parent">
                    <div class="card-box reveal reveal-delay-1">
                        <span>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                        </span>

                        <p>"The loan process was so simple and the rates are genuinely better than any bank. KPMPCATS
                            has
                            helped my family through so many milestones."</p>

                        <div class="card-account">
                            <div class="card-icon">
                                <p>M</p>
                            </div>
                            <div class="card-text">
                                <p>Maria Santos</p>
                                <span>Member since 2011</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-box reveal reveal-delay-2">
                        <span>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                        </span>

                        <p>"The loan process was so simple and the rates are genuinely better than any bank. KPMPCATS
                            has
                            helped my family through so many milestones."</p>

                        <div class="card-account">
                            <div class="card-icon">
                                <p>M</p>
                            </div>
                            <div class="card-text">
                                <p>Maria Santos</p>
                                <span>Member since 2011</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-box reveal reveal-delay-1">
                        <span>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                        </span>

                        <p>"The loan process was so simple and the rates are genuinely better than any bank. KPMPCATS
                            has
                            helped my family through so many milestones."</p>

                        <div class="card-account">
                            <div class="card-icon">
                                <p>M</p>
                            </div>
                            <div class="card-text">
                                <p>Maria Santos</p>
                                <span>Member since 2011</span>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="card-box-text">
                        <div class="parent-active">
                            <div class="active-members reveal-scale">
                                <span>5,000+</span>
                                <p>Active Members</p>
                            </div>

                            <div class="active-members reveal-scale reveal-delay-1">
                                <span>₱500M+</span>
                                <p>Total Savings</p>
                            </div>

                            <div class="active-members reveal-scale">
                                <span>₱300M+</span>
                                <p>Loan Disbursed</p>
                            </div>

                            <div class="active-members reveal-scale reveal-delay-1">
                                <span>15 Years</span>
                                <p>Serving Community</p>
                            </div>
                        </div>
                        <p>Our cooperative has been a pillar of financial stability for thousands of families,
                            providing
                            accessible financial services and fostering a culture of savings and mutual aid.</p>
                    </div>
                    <div class="card-box-image">
                        <img src="images/empowering.jpg" alt="">
                    </div> --}}
                </div>
            </div>
        </section>

        <div class="officers-section">
            <div class="officers-header">
                <div class="officers-tag">Officers</div>
                <h2>Meet the <b>Team</b></h2>
                <p>The dedicated leaders guiding KPMPCATS toward sustainable growth and member-first service.</p>
            </div>

            <div class="officers-grid">
                @forelse($officers as $officer)
                    @php
                        $name = trim(($officer->user->first_name ?? '') . ' ' . ($officer->user->last_name ?? ''));
                    @endphp
                    <div class="officer-card reveal">
                        <div class="officer-avatar">
                            <i class="fa fa-user"></i>
                        </div>

                        <h4>{{ $name }}</h4>
                        <span class="officer-position">{{ $officer->position }}</span>

                        @if($officer->term_start || $officer->term_end)
                            <p class="officer-term">
                                {{ $officer->term_start ? $officer->term_start->format('Y') : 'Present' }} –
                                {{ $officer->term_end ? $officer->term_end->format('Y') : 'Present' }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="officers-empty">
                        <p>Leadership information coming soon.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <section id="section5">
            <h2 class="text-center">Ready to Join <b>Our Community?</b></h2>

            <div class="d-flex justify-content-center align-items-center">
                <p class="text-center">Start your journey towards financial security and prosperity. Become a
                    member today and experience the cooperative difference.</p>
            </div>

            <div class="become-buttons">
                <a href="{{ route("RegisterPage") }}">Become a Member <i class="fa fa-arrow-right"></i></a>

                <a href="{{ route("LoginPage") }}">Go to Login <i class="fa fa-arrow-right"></i></a>
            </div>
        </section>

        {{-- <footer>

        </footer> --}}
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.slide');
            const images = document.querySelectorAll('.slide-img');
            const dots = document.querySelectorAll('.dot');
            let current = 0;
            let timer;

            function goToSlide(index) {
                slides[current].classList.remove('active');
                images[current].classList.remove('active');
                dots[current].classList.remove('active');

                current = index;

                slides[current].classList.add('active');
                images[current].classList.add('active');
                dots[current].classList.add('active');
            }

            function nextSlide() {
                goToSlide((current + 1) % slides.length);
            }

            function startAutoplay() {
                timer = setInterval(nextSlide, 5000);
            }

            dots.forEach((dot, i) => {
                dot.addEventListener('click', () => {
                    clearInterval(timer);
                    goToSlide(i);
                    startAutoplay();
                });
            });

            startAutoplay();
        });
    </script>

    <!-- <script>
        const track = document.getElementById('carouselTrack');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const slideNum = document.getElementById('slideNum');
        const progress = document.getElementById('progress');
        const total = slides.length;
        let current = 0;
        let timer;

        function goTo(idx) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = (idx + total) % total;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
            track.style.transform = translateX(-${current * 100}%);
            slideNum.textContent = String(current + 1).padStart(2, '0');
            progress.style.transition = 'none';
            progress.style.width = '0%';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    progress.style.transition = 'width 5s linear';
                    progress.style.width = '100%';
                });
            });
        }

        function startAuto() {
            clearInterval(timer);
            timer = setInterval(() => goTo(current + 1), 5000);
        }

        prevBtn.addEventListener('click', () => { goTo(current - 1); startAuto(); });
        nextBtn.addEventListener('click', () => { goTo(current + 1); startAuto(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); startAuto(); }));

        track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
        track.addEventListener('touchend', e => {
            const diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) { goTo(diff > 0 ? current + 1 : current - 1); startAuto(); }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') { goTo(current - 1); startAuto(); }
            if (e.key === 'ArrowRight') { goTo(current + 1); startAuto(); }
        });

        goTo(0);
        startAuto();
    </script> -->
    
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('backToTop');
            const ring = btn.querySelector('.ring-bar');
            const CIRCUMFERENCE = 144.5;
            const SHOW_AFTER = 300; // px scrolled before the button appears
    
            function updateButton() {
                const scrollTop = window.scrollY;
                const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
                const progress = maxScroll > 0 ? scrollTop / maxScroll : 0;
    
                btn.classList.toggle('show', scrollTop > SHOW_AFTER);
                ring.style.strokeDashoffset = CIRCUMFERENCE - (CIRCUMFERENCE * progress);
            }
    
            window.addEventListener('scroll', updateButton, { passive: true });
            updateButton();
    
            btn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>

    <script nonce="{{ csp_nonce() }}">
        const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target); // animate once only
                }
            });
        }, { threshold: 0.13 });

        revealEls.forEach(el => observer.observe(el));
    </script>

    {{--
    <script>
        const nav = document.querySelector(".navbar");

        let lastScrollY = window.scrollY;

        window.addEventListener('scroll', () => {
            if (lastScrollY < window.scrollY) {
                nav.classList.add('nav-hidden');
            } else {
                nav.classList.remove('nav-hidden');
            }

            lastScrollY = window.scrollY;
        });

    </script> --}}

    {{-- AOS animation link js --}}
    {{--
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init();
    </script> --}}

    {{--
    <script src="style.js"></script> --}}
</body>

</html>