document.addEventListener('DOMContentLoaded', function () {
  const galleries = document.querySelectorAll('[data-gallery]');

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
