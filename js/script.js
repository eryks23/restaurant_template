// === 1. Preloader: Hide when fully loaded and unlock scrolling ===
var preloader = document.getElementById('preloader');

if (document.readyState === 'loading') {
  document.body.classList.add('loading');
}

window.addEventListener('load', function () {
  if (!preloader) return;

  preloader.style.transition = 'opacity 0.5s ease';
  preloader.style.opacity = '0';

  setTimeout(function () {
    preloader.style.display = 'none';
    document.body.classList.remove('loading');
  }, 500);
});


// === 2. Form Validation and Success Message ===
var form = document.querySelector('form');
var successMsg = document.getElementById('form-success');

if (form && successMsg) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    successMsg.style.display = 'none';

    var name = form.querySelector('[name="name"]').value.trim();
    var email = form.querySelector('[name="email"]').value.trim();
    var phone = form.querySelector('[name="phone"]').value.trim();

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phoneRegex = /^[\d+\-\s()]+$/;

    if (!name || !emailRegex.test(email) || !phoneRegex.test(phone)) {
      alert("Please enter valid contact information.");
      return;
    }

    successMsg.style.display = 'block';
    form.reset();
  });
}


// === 3. Hamburger Menu Toggle (Responsive Navigation) ===
var toggleBtn = document.getElementById('menu-toggle');
var navBar = document.getElementById('nav-bar');

if (toggleBtn && navBar) {
  toggleBtn.addEventListener('click', function () {
    var isActive = navBar.classList.toggle('active');
    toggleBtn.setAttribute('aria-expanded', isActive);
  });
}


// === 4. Minimum Date Picker ===
var dateInput = document.getElementById('date');

if (dateInput) {
  var today = new Date();
  var yyyy = today.getFullYear();
  var mm = String(today.getMonth() + 1).padStart(2, '0');
  var dd = String(today.getDate()).padStart(2, '0');
  var formattedToday = yyyy + '-' + mm + '-' + dd;
  dateInput.min = formattedToday;
}


// === 5. Live Phone Number Validation ===
var phoneInput = document.getElementById('phone');
var phoneError = document.getElementById('phone-error');
var phoneSuccess = document.getElementById('phone-success');

if (phoneInput && phoneError && phoneSuccess) {
  phoneInput.addEventListener('input', function () {
    var phonePattern = /^\+?[0-9\s\-]{7,15}$/;

    if (phonePattern.test(phoneInput.value)) {
      phoneError.style.display = 'none';
      phoneSuccess.style.display = 'inline';
    } else {
      phoneSuccess.style.display = 'none';
      phoneError.style.display = 'inline';
    }
  });
}

// === 6. Swiper Slider Initialization ===
var swiper = new Swiper(".Swiper", {
    slidesPerView: "auto",
    spaceBetween: 10,
    loop: true,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    }
  });