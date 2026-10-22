<nav
    class="tw:flex justify-content-between align-items-center tw:w-[100%] tw:px-[2rem] tw:h-[70px] tw:border-b-1 tw:border-solid tw:border-[rgba(0,0,0,0.2)] tw:bg-[#ffffff] tw:p-[1rem]">
    <div class="nav-logo">
        {{-- <h2 class="m-0" style="font-size: 25px">LOGO</h2> --}}
        <img src="images/logo2.png" width="50px" height="50px" style="border-radius: 50%" alt="">
        {{-- <h2 class="mw-100 m-0" style="font-size: 14px; width: 200px;">Kingsland Pala-Pala MPC & Transport Service</h2> --}}
    </div>

    <div class="nav-list">
        <ul class="tw:flex tw:gap-x-[4rem] m-0 p-0">
            <li class="tw:list-none">
                <a href="{{ route("MemberPortal") }}"
                    class="tw:no-underline tw:text-[15.5px] text-decoration-none text-black">Home</a>
            </li>

            <li class="tw:list-none">
                <a href="{{ route("LoanApplication") }}"
                    class="tw:no-underline tw:text-[15.5px] text-decoration-none text-black">Routes</a>
            </li>

            <li class="tw:list-none">
                <a href="{{ route("Savings") }}" class="tw:no-underline tw:text-[15.5px] text-decoration-none text-black">Savings</a>
            </li>
        </ul>
    </div>

    <div class="nav-acc3">

        {{-- <a href="{{ route(" LoginPage") }}" class="py-2 px-5 text-decoration-none tw:text-[15.5px] fw-semibold"
            style="border-radius: 10px">Login</a> --}}
        <ul class="m-0 p-0">
            <li>
                <a href="#" class="tw:flex tw:justify-center tw:items-center tw:gap-x-[0.7rem]">
                    <i class="fa fa-bell" style="font-size: 20px"></i>
                    <img src="images/unnamed.png" width="35px" height="35px" style="border-radius: 50%" alt="">
                    <p style="margin: 0">Jhun</p>
                </a>

                <ul>
                    <li>
                        <div class="card-icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <a href="{{ route("ProfileMember") }}">Profile</a>
                    </li>
                    <li>
                        <div class="card-icon">
                            <i class="fa fa-lock"></i>
                        </div>
                        <a href="#">Reset Password</a>
                    </li>

                    <hr>

                    <li>
                        <div class="card-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="{{ route("MemberPortal") }}">Switch to Member Portal</a>
                    </li>

                    <hr>

                    <li>
                        <div class="card-icon">
                            <i class="fa fa-sign-out"></i>
                        </div>
                        <a href="{{ route("LoginPage") }}">Logout</a>
                    </li>
                </ul>

            </li>
            
        </ul>
    </div>
</nav>