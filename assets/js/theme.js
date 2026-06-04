document.addEventListener('DOMContentLoaded', function () {
  const galleries = document.querySelectorAll('[data-gallery]');
  const filterToggle = document.querySelector('[data-filter-toggle]');
  const advancedFilters = document.getElementById('tmg-advanced-filters');
  const googleAuth = document.querySelector('[data-google-auth]');

  if (filterToggle && advancedFilters) {
    const hasActiveAdvancedFilter = Array.from(
      advancedFilters.querySelectorAll('input, select')
    ).some(function (field) {
      return field.value !== '';
    });

    const setFiltersState = function (expanded) {
      filterToggle.classList.toggle('is-active', expanded);
      filterToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      advancedFilters.hidden = !expanded;
    };

    setFiltersState(window.innerWidth >= 861 || hasActiveAdvancedFilter);

    filterToggle.addEventListener('click', function () {
      const expanded = filterToggle.getAttribute('aria-expanded') === 'true';
      setFiltersState(!expanded);
    });
  }

  if (
    googleAuth &&
    window.google &&
    window.google.accounts &&
    window.google.accounts.id &&
    window.tmgRentals &&
    window.tmgRentals.googleClientId
  ) {
    const handleGoogleCredential = function (response) {
      const formData = new FormData();
      formData.append('action', 'tmg_google_auth');
      formData.append('nonce', window.tmgRentals.googleNonce);
      formData.append('credential', response.credential);

      fetch(window.tmgRentals.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (data.success && data.data && data.data.redirect) {
            window.location.href = data.data.redirect;
            return;
          }

          window.alert(
            (data.data && data.data.message) || window.tmgRentals.googleError
          );
        })
        .catch(function () {
          window.alert(window.tmgRentals.googleError);
        });
    };

    window.google.accounts.id.initialize({
      client_id: window.tmgRentals.googleClientId,
      callback: handleGoogleCredential
    });

    window.google.accounts.id.renderButton(
      document.getElementById('tmg-google-auth-button'),
      {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        shape: 'pill',
        width: 320,
        locale: 'ar'
      }
    );
  }

  galleries.forEach(function (gallery) {
    const slides = Array.from(gallery.querySelectorAll('.tmg-gallery__slide'));
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
    const prev = gallery.querySelector('[data-gallery-prev]');
    const next = gallery.querySelector('[data-gallery-next]');
    let current = 0;

    if (!slides.length) {
      return;
    }

    const setActive = function (index) {
      current = (index + slides.length) % slides.length;

      slides.forEach(function (slide, slideIndex) {
        slide.classList.toggle('is-active', slideIndex === current);
      });

      thumbs.forEach(function (thumb, thumbIndex) {
        thumb.classList.toggle('is-active', thumbIndex === current);
      });
    };

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        setActive(Number(thumb.dataset.galleryThumb));
      });
    });

    if (prev) {
      prev.addEventListener('click', function () {
        setActive(current - 1);
      });
    }

    if (next) {
      next.addEventListener('click', function () {
        setActive(current + 1);
      });
    }
  });
});
