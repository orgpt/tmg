document.addEventListener('DOMContentLoaded', function () {
  const galleries = document.querySelectorAll('[data-gallery]');
  const filterToggle = document.querySelector('[data-filter-toggle]');
  const advancedFilters = document.getElementById('tmg-advanced-filters');

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
