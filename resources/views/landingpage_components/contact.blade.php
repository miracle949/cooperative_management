<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contact</title>

    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/contact.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.navbar")
        @include("components.offcanvas")

        <main>

            <div class="row">

                <div
                    class="col-lg-6 col-md-12">
                    <h3 class="fw-bold">Send Us a Message</h3>

                    <p class="mt-3">Fill out the form below and we'll get back to you as soon as possible.</p>

                    <div class="mt-4">
                        <label>Fullname *</label>
                        <input type="text" class="form-control mt-2" name="" placeholder="Juan dela Cruz">
                    </div>

                    <div class="mt-4">
                        <label>Email Address *</label>
                        <input type="text" class="form-control mt-2" name="" placeholder="juan@gmail.com">
                    </div>

                    <div class="mt-4">
                        <label>Phone Number *</label>
                        <input type="text" class="form-control mt-2" name="" placeholder="+63 917 123 4567">
                    </div>

                    <div class="mt-4">
                        <label>Subject *</label>
                        <select class="form-select mt-2" id="">
                            <option value="">Select a subject</option>
                            <option value="">General Inquiry</option>
                            <option value="">Membership Information</option>
                            <option value="">Loan Application</option>
                            <option value="">Savings Account</option>
                            <option value="">Complain or Concern</option>
                            <option value="">Feedback</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label>Message *</label>
                        <textarea name="" class="form-control mt-2 p-3 tw:h-[200px]"
                            placeholder="Tell us how we can help you..."></textarea>
                    </div>

                    <div class="mt-4">
                        <button class="btn tw:w-full"><i class="fa-brands fa-telegram-plane"></i> Send
                            Message</button>
                    </div>
                </div>

                <div
                    class="col-lg-6 col-md-12 contact ps-lg-5 ps-md-0 mt-md-5 mt-lg-0">
                    <h3 class="fw-bold">Contact Information</h3>

                    <div class="tw:flex gap-3 mt-4">
                        <div class="p-4 d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                            <i class="fa fa-location-dot"></i>
                        </div>

                        <div>
                            <h4>Visit Us</h4>

                            <p class="mt-3">123 Cooperative Building</p>
                            <p>Main Street, Business District</p>
                            <p>Metro Manila, Philippines 1000</p>
                        </div>
                    </div>

                    <div class="tw:flex gap-3 mt-4">
                        <div class="p-4 d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                            <i class="fa fa-phone"></i>
                        </div>

                        <div>
                            <h4>Call Us</h4>

                            <p class="mt-3">Main Office: (02) 1234-5678</p>
                            <p>Mobile: +63 917 123 4567</p>
                            <p>Hotline: 1-800-COOP-123</p>
                        </div>
                    </div>

                    <div class="tw:flex gap-3 mt-4">
                        <div class="p-4 d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                            <i class="fa fa-envelope"></i>
                        </div>

                        <div>
                            <h4>Email Us</h4>

                            <p class="mt-3">info@memberscoop.ph</p>
                            <p>support@memberscoop.ph</p>
                            <p>loans@memberscoop.ph</p>
                        </div>
                    </div>

                    <div class="tw:flex gap-3 mt-4">
                        <div class="p-4 d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                            <i class="fa fa-clock"></i>
                        </div>

                        <div>
                            <h4>Office Hours</h4>

                            <p class="mt-3">Monday - Friday: 8:00 AM - 5:00 PM</p>
                            <p>Saturday: 8:00 AM - 12:00 PM</p>
                            <p>Sunday & Holidays: Closed</p>
                        </div>
                    </div>

                    <div class="p-4 social mt-4" style="border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                        <h4>Follow Us</h4>

                        <div class="d-flex column-gap-3">
                            <i class="fa-brands fa-facebook"></i>
                            <i class="fa-brands fa-twitter"></i>
                            <i class="fa-brands fa-instagram"></i>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <section class="p-5 pt-5" id="section1">
            <h3 class="text-center">Our Branches</h3>

            <p class="mt-3 tw:text-[#808080] text-center">Visit any of our convenient branch locations to speak with our
                team in person.</p>

            <div class="mt-5 d-flex justify-content-center align-items-center gap-5 flex-wrap">
                <div data-aos="fade-up" data-aos-duration="1000" class="tw:bg-white p-4"
                    style="border: 1px solid rgba(0,0,0,0.3); width: 320px; height: 310px; border-radius: 20px;">

                    <div class="p-4 d-flex justify-content-center align-items-center"
                        style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                        <i class="fa fa-location-dot"></i>
                    </div>

                    <h4 class="mt-4">Main Office</h4>

                    <p class="mt-4">123 Cooperative Building, Main Street, Metro Manila</p>

                    <span class="mt-4">(02) 1234-5678</span>

                    <p class="mt-4">Mon-Fri: 8AM-5PM, Sat: 8AM-12PM</p>
                </div>

                <div data-aos="fade-up" data-aos-duration="1000" class="tw:bg-white p-4"
                    style="border: 1px solid rgba(0,0,0,0.3); width: 320px; height: 310px; border-radius: 20px;">
                    <div class="p-4 d-flex justify-content-center align-items-center"
                        style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                        <i class="fa fa-location-dot"></i>
                    </div>

                    <h4 class="mt-4">Quezon City Branch</h4>

                    <p class="mt-4">456 QC Avenue, Quezon City</p>

                    <span class="mt-4">(02) 8765-4321</span>

                    <p class="mt-4">Mon-Fri: 9AM-5PM, Sat: 9AM-12PM</p>
                </div>

                <div data-aos="fade-up" data-aos-duration="1000" class="tw:bg-white p-4"
                    style="border: 1px solid rgba(0,0,0,0.3); width: 320px; height: 310px; border-radius: 20px;">
                    <div class="p-4 d-flex justify-content-center align-items-center"
                        style="width: 50px; height: 50px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background-color: var(--green)">
                        <i class="fa fa-location-dot"></i>
                    </div>

                    <h4 class="mt-4">Makati Branch</h4>

                    <p class="mt-4">789 Business Plaza, Makati City</p>

                    <span class="mt-4">(02) 2468-1357</span>

                    <p class="mt-4">Mon-Fri: 8AM-5PM</p>
                </div>
            </div>
        </section>

        <section class="p-5 pt-5" id="section2">
            <h3 class="text-center mt-5">Find Us on the Map</h3>

            <div class="mt-4">
                <iframe data-aos="fade-up" data-aos-duration="1000"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3181.57745427514!2d120.95656497412529!3d14.298605386152717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d57dea34374b%3A0xc8a2a21c6105ede0!2sKingsland%20Palapala%20Multipurpose%20Cooperative%20and%20Transport%20Services!5e1!3m2!1sen!2sph!4v1766853394642!5m2!1sen!2sph"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>

        <section class="p-5 pt-5" id="section3">
            <h3 class="text-center mt-5">Frequently Asked Questions</h3>

            <p class="text-center mt-3 tw:text-[#808080]">Quick answers to common questions. Can't find what you're
                looking for? Contact us!</p>

            <div class="accordion mt-4" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            How do I become a member?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p>You can apply for membership by visiting any of our branches with valid IDs and initial
                                deposit.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            What are the loan requirements?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p>Requirements vary by loan type. Generally, you need to be a member in good standing with
                                sufficient savings.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            How long does loan approval take?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p>Most loan applications are processed within 3-5 business days after submission of
                                complete requirements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    {{-- AOS animation link js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    {{-- Bootstrap link --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script> --}}

    <script nonce="{{ csp_nonce() }}">
        AOS.init();
    </script>
</body>

</html>