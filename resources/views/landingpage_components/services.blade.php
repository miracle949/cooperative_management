<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Services</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="../css_folder/services.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid p-0">

        @include("components.navbar")
        @include("components.offcanvas")

        <main>
            <div class="services-parent">
                <div class="card">
                    <div class="card-icon">
                        <i class="fa fa-wallet"></i>
                    </div>

                    <h3>Savings Accounts</h3>

                    <p>Grow your wealth with competitive interest rates and flexible savings options.</p>

                    <ul>
                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Regular Savings Account</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Time Deposit</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Special Savings Programs</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Automatic Monthly Savings</span>
                        </li>
                    </ul>

                    <hr>

                    <summary>Starting at 8%</summary>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa fa-arrow-trend-up"></i>
                    </div>

                    <h3>Personal Lending</h3>

                    <p>Grow your wealth with competitive interest rates and flexible savings options.</p>

                    <ul>
                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Regular Savings Account</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Time Deposit</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Special Savings Programs</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Automatic Monthly Savings</span>
                        </li>
                    </ul>

                    <hr>

                    <summary>Starting at 8%</summary>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa fa-heart"></i>
                    </div>

                    <h3>Emergency Lending</h3>

                    <p>Grow your wealth with competitive interest rates and flexible savings options.</p>

                    <ul>
                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Regular Savings Account</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Time Deposit</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Special Savings Programs</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Automatic Monthly Savings</span>
                        </li>
                    </ul>

                    <hr>

                    <summary>Starting at 8%</summary>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa fa-piggy-bank"></i>
                    </div>

                    <h3>Lending Programs</h3>

                    <p>Grow your wealth with competitive interest rates and flexible savings options.</p>

                    <ul>
                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Regular Savings Account</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Time Deposit</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Special Savings Programs</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Automatic Monthly Savings</span>
                        </li>
                    </ul>

                    <hr>

                    <summary>Starting at 8%</summary>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>

                    <h3>Insurance Programs</h3>

                    <p>Grow your wealth with competitive interest rates and flexible savings options.</p>

                    <ul>
                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Regular Savings Account</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Time Deposit</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Special Savings Programs</span>
                        </li>

                        <li>
                            <i class="fa fa-check-circle"></i>
                            <span>Automatic Monthly Savings</span>
                        </li>
                    </ul>

                    <hr>

                    <summary>Starting at 8%</summary>
                </div>
            </div>

            <div class="choosing-our">
                <div class="choosing-text">
                    <h3>Why Choose Our Services?</h3>

                    <p>As a member-owned cooperative, our services are designed with your best interests in mind. We
                        prioritize accessibility, affordability, and transparency in everything we do.</p>

                    <ul>
                        <div class="check-choose">
                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Lower interest rates compared to banks</span>
                            </li>

                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Transparent operations</span>
                            </li>

                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Profit-sharing through dividends</span>
                            </li>
                        </div>

                        <div class="check-choose">
                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Member-centric approach</span>
                            </li>

                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Democratic decision-making</span>
                            </li>

                            <li>
                                <i class="fa fa-check-circle"></i>
                                <span>Financial literacy programs</span>
                            </li>
                        </div>
                    </ul>
                </div>

                <div class="choosing-parent">
                    <div class="choosing-image">
                        <h2>Hello</h2>
                    </div>
                </div>
            </div>

            <div class="how-access">
                <h3>How to Access Our Services</h3>
                <p>Getting started with our services is simple and straightforward. Follow these easy steps.</p>

                <div class="step-by-step">
                    <div class="step-card">
                        <div class="step-icon">
                            <p>1</p>
                        </div>

                        <h3>Become a Member</h3>
                        <p>Open a savings account and purchase cooperative shares to join our community.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-icon">
                            <p>2</p>
                        </div>

                        <h3>Build Your Credit</h3>
                        <p>Regular savings deposits strengthen your eligibility for larger loan amounts.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-icon">
                            <p>3</p>
                        </div>

                        <h3>Apply for Services</h3>
                        <p>Submit your application online or visit our office with required documents.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-icon">
                            <p>4</p>
                        </div>

                        <h3>Get Approved</h3>
                        <p>Quick processing and approval, with funds released within days.</p>
                    </div>
                </div>
            </div>

            <div class="ready-started">
                <h3>Ready to Get Started?</h3>

                <p>Join our cooperative today and gain access to all our financial services.</p>

                <div class="ready-started-acc">
                    <a href="#">Member Login</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
        </main>

    </div>


    {{-- AOS animation link js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    {{-- Bootstrap link --}}
    {{-- <script defer src="../bootstrap_folder/js/bootstrap.bundle.min.js"></script> --}}
</body>

</html>