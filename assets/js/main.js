/* =============================================
   EMCEE WEBSITE — MAIN JAVASCRIPT
   ============================================= */

/* ---- Navbar: scroll effect + mobile toggle ---- */
(function () {
  const navbar    = document.getElementById('navbar');
  const navToggle = document.getElementById('navToggle');
  const navLinks  = document.getElementById('navLinks');

  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      const isOpen = navLinks.classList.toggle('open');
      navToggle.classList.toggle('open', isOpen);
      navToggle.setAttribute('aria-expanded', isOpen);
    });

    // Close menu when a link is clicked
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('open');
        navToggle.classList.remove('open');
        navToggle.setAttribute('aria-expanded', false);
      });
    });
  }
})();

/* ---- Scroll animations (Intersection Observer) ---- */
(function () {
  const targets = document.querySelectorAll('.fade-in');
  if (!targets.length) return;

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  targets.forEach(function (el) {
    observer.observe(el);
  });
})();

/* ---- Package pre-selection from URL param ---- */
(function () {
  const params  = new URLSearchParams(window.location.search);
  const pkg     = params.get('package');
  if (!pkg) return;

  const radio = document.querySelector('input[name="package"][value="' + pkg + '"]');
  if (radio) {
    radio.checked = true;
    const label = radio.closest('.radio-option');
    if (label) label.classList.add('selected');
  }
})();

/* ---- Radio option highlight ---- */
(function () {
  const radios = document.querySelectorAll('.radio-option input[type="radio"]');
  radios.forEach(function (radio) {
    radio.addEventListener('change', function () {
      // Remove selected from all
      document.querySelectorAll('.radio-option').forEach(function (opt) {
        opt.classList.remove('selected');
      });
      // Add to current
      const label = radio.closest('.radio-option');
      if (label) label.classList.add('selected');
    });
  });
})();

/* ---- Set min date on date input (today) ---- */
(function () {
  const dateInput = document.getElementById('eventDate');
  if (!dateInput) return;
  const today = new Date().toISOString().split('T')[0];
  dateInput.setAttribute('min', today);
})();

/* ---- Booking form validation & submission ---- */
(function () {
  const form    = document.getElementById('bookingForm');
  const formCard = document.getElementById('formCard');
  const success  = document.getElementById('successMessage');
  if (!form) return;

  function showError(fieldId, errorId, show) {
    const field = document.getElementById(fieldId);
    const err   = document.getElementById(errorId);
    if (!field || !err) return;
    if (show) {
      field.classList.add('error');
      err.classList.add('visible');
    } else {
      field.classList.remove('error');
      err.classList.remove('visible');
    }
  }

  function isValidEmail(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
  }

  function validateForm() {
    let valid = true;

    // Full Name
    const fullName = document.getElementById('fullName');
    if (!fullName || !fullName.value.trim()) {
      showError('fullName', 'fullNameError', true);
      valid = false;
    } else {
      showError('fullName', 'fullNameError', false);
    }

    // Email
    const email = document.getElementById('email');
    if (!email || !isValidEmail(email.value)) {
      showError('email', 'emailError', true);
      valid = false;
    } else {
      showError('email', 'emailError', false);
    }

    // Phone
    const phone = document.getElementById('phone');
    if (!phone || !phone.value.trim()) {
      showError('phone', 'phoneError', true);
      valid = false;
    } else {
      showError('phone', 'phoneError', false);
    }

    // Event Type
    const eventType = document.getElementById('eventType');
    if (!eventType || !eventType.value) {
      showError('eventType', 'eventTypeError', true);
      valid = false;
    } else {
      showError('eventType', 'eventTypeError', false);
    }

    // Venue
    const venue = document.getElementById('venue');
    if (!venue || !venue.value.trim()) {
      showError('venue', 'venueError', true);
      valid = false;
    } else {
      showError('venue', 'venueError', false);
    }

    // Event Date
    const eventDate = document.getElementById('eventDate');
    if (!eventDate || !eventDate.value) {
      showError('eventDate', 'eventDateError', true);
      valid = false;
    } else {
      showError('eventDate', 'eventDateError', false);
    }

    // Event Time
    const eventTime = document.getElementById('eventTime');
    if (!eventTime || !eventTime.value) {
      showError('eventTime', 'eventTimeError', true);
      valid = false;
    } else {
      showError('eventTime', 'eventTimeError', false);
    }

    // Duration
    const duration = document.getElementById('duration');
    if (!duration || !duration.value) {
      showError('duration', 'durationError', true);
      valid = false;
    } else {
      showError('duration', 'durationError', false);
    }

    // Package
    const packageSelected = document.querySelector('input[name="package"]:checked');
    const packageError    = document.getElementById('packageError');
    if (!packageSelected) {
      if (packageError) packageError.classList.add('visible');
      valid = false;
    } else {
      if (packageError) packageError.classList.remove('visible');
    }

    return valid;
  }

  // Live validation: clear errors on input/change
  ['fullName','email','phone','eventType','venue','eventDate','eventTime','duration'].forEach(function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () {
      const errorId = id + 'Error';
      // Re-validate only this field on blur/input
      el.classList.remove('error');
      const err = document.getElementById(errorId);
      if (err) err.classList.remove('visible');
    });
    el.addEventListener('change', function () {
      el.classList.remove('error');
      const err = document.getElementById(id + 'Error');
      if (err) err.classList.remove('visible');
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!validateForm()) {
      // Scroll to first error
      const firstError = form.querySelector('.error, input[name="package"]');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    // Simulate submission (replace with real fetch/API call as needed)
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
      submitBtn.textContent = 'Sending...';
      submitBtn.disabled = true;
    }

    setTimeout(function () {
      if (formCard) formCard.style.display = 'none';
      if (success)  success.classList.add('visible');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 800);
  });
})();
