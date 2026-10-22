{{-- offcanvas 1 : public --}}
<div class="offcanvas offcanvas-end kp-offcanvas" data-bs-backdrop="static" tabindex="-1" id="offcanvasMenu1"
  aria-labelledby="offcanvasMenu1Label">

  <div class="kp-oc-header">
    <div class="kp-oc-brand">
      <img src="images/logo2.png" alt="KPMPCATS logo">
      <div>
        <h5 id="offcanvasMenu1Label">KPMPCATS</h5>
        {{-- <small>Multipurpose Cooperative</small> --}}
      </div>
    </div>
    <button type="button" class="kp-oc-close" data-bs-dismiss="offcanvas" aria-label="Close">
      <i class="fa fa-xmark"></i>
    </button>
  </div>

  <div class="kp-oc-body">

    <nav class="kp-oc-links">
      <a href="{{ route('index') }}" class="{{ request()->routeIs('index') ? 'is-active' : '' }}">
        <span class="kp-oc-icon"><i class="fa fa-house"></i></span>
        <span class="kp-oc-text">Home</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="#section3">
        <span class="kp-oc-icon"><i class="fa fa-circle-info"></i></span>
        <span class="kp-oc-text">About Us</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="#how-it-works-parent">
        <span class="kp-oc-icon"><i class="fa fa-list-check"></i></span>
        <span class="kp-oc-text">How it Works</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="#products_services">
        <span class="kp-oc-icon"><i class="fa fa-briefcase"></i></span>
        <span class="kp-oc-text">Products & Services</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="#section6">
        <span class="kp-oc-icon"><i class="fa fa-file-signature"></i></span>
        <span class="kp-oc-text">What we offer</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
    </nav>

    <div class="kp-oc-actions">
      <a href="{{ route('LoginPage') }}" class="kp-oc-btn kp-oc-btn-outline">
        <i class="fa fa-right-to-bracket"></i> Login
      </a>
      <a href="{{ route('RegisterPage') }}" class="kp-oc-btn kp-oc-btn-solid">
        <i class="fa fa-user-plus"></i> Become a Member
      </a>
    </div>
  </div>
</div>

{{-- offcanvas 2 : member portal --}}
<div class="offcanvas offcanvas-end kp-offcanvas" data-bs-backdrop="static" tabindex="-1" id="offcanvasMenu2"
  aria-labelledby="offcanvasMenu2Label">

  <div class="kp-oc-header">
    <div class="kp-oc-brand">
      <img src="images/logo2.png" alt="KPMPCATS logo">
      <div>
        <h5 id="offcanvasMenu2Label">KPMPCATS</h5>
        <small>Member Portal</small>
      </div>
    </div>
    <button type="button" class="kp-oc-close" data-bs-dismiss="offcanvas" aria-label="Close">
      <i class="fa fa-xmark"></i>
    </button>
  </div>

  <div class="kp-oc-body">
    <p class="kp-oc-label">Portal</p>

    <nav class="kp-oc-links">
      <a href="{{ route('MemberPortal') }}" class="{{ request()->routeIs('MemberPortal') ? 'is-active' : '' }}">
        <span class="kp-oc-icon"><i class="fa fa-house"></i></span>
        <span class="kp-oc-text">Home</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="{{ route('LoanApplication') }}" class="{{ request()->routeIs('LoanApplication') ? 'is-active' : '' }}">
        <span class="kp-oc-icon"><i class="fa fa-file-signature"></i></span>
        <span class="kp-oc-text">Loan Application</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="{{ route('savings.index') }}" class="{{ request()->routeIs('savings.index') ? 'is-active' : '' }}">
        <span class="kp-oc-icon"><i class="fa fa-piggy-bank"></i></span>
        <span class="kp-oc-text">Savings</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
      <a href="{{ route('LoanStatus') }}" class="{{ request()->routeIs('LoanStatus') ? 'is-active' : '' }}">
        <span class="kp-oc-icon"><i class="fa fa-chart-simple"></i></span>
        <span class="kp-oc-text">Loan Status</span>
        <i class="fa fa-arrow-right kp-oc-arrow"></i>
      </a>
    </nav>

    <div class="kp-oc-actions">
      <a href="{{ route('LoginPage') }}" class="kp-oc-btn kp-oc-btn-solid">
        <i class="fa fa-right-to-bracket"></i> Login
      </a>
    </div>
  </div>
</div>
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('DOMContentLoaded', () => {

        // closes the offcanvas through Bootstrap using a temporary hidden dismiss button
        const closeMenu = (el) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.hidden = true;
            btn.setAttribute('data-bs-dismiss', 'offcanvas');
            el.appendChild(btn);
            btn.click();
            btn.remove();
        };

        document.querySelectorAll('.kp-oc-links a, .kp-oc-actions a').forEach(link => {
            link.addEventListener('click', e => {
                const href = link.getAttribute('href');
                const el = link.closest('.offcanvas');

                if (href && href.startsWith('#')) {
                    // section link: close the menu, then scroll
                    e.preventDefault();
                    let done = false;
                    const scrollTo = () => {
                        if (done) return;
                        done = true;
                        document.querySelector(href)?.scrollIntoView({ behavior: 'smooth' });
                    };
                    el.addEventListener('hidden.bs.offcanvas', scrollTo, { once: true });
                    setTimeout(scrollTo, 700);
                }

                // page links are not blocked, so the browser navigates normally
                closeMenu(el);
            });
        });
    });
</script>