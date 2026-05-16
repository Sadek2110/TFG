(function() {
  'use strict';

  if (window.FastPlayScrollAnim) return;
  window.FastPlayScrollAnim = {
    init: init,
  };

  let _config;
  let video, progressBar, ticking, reduceMotion;

  function init(config) {
    _config = config || {};
    video = document.getElementById('heroVideo');
    if (!video) return;
    progressBar = document.getElementById('scrollProgress');
    reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    video.pause();
    video.currentTime = 0;
    if (video.readyState >= 1) markReady();
    else video.addEventListener('loadedmetadata', markReady, { once: true });
    video.addEventListener('loadeddata', markReady, { once: true });
    window.addEventListener('scroll', onScroll, { passive: true });

    setTimeout(function() {
      if (video && !video.classList.contains('is-ready')) {
        video.classList.add('is-ready');
      }
    }, 4000);

    initObserver();
    if (_config.onReady) _config.onReady();
  }

  function markReady() {
    if (video) video.classList.add('is-ready');
  }

  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function() {
      const scrollTop = window.scrollY;
      const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
      const scrollFraction = maxScroll > 0 ? Math.min(scrollTop / maxScroll, 1) : 0;
      if (progressBar) progressBar.style.width = (scrollFraction * 100) + '%';
      if (!reduceMotion && video && video.duration && isFinite(video.duration)) {
        video.currentTime = Math.min(video.duration * scrollFraction, Math.max(video.duration - 0.05, 0));
      }
      if (_config.onScroll) _config.onScroll(scrollTop, scrollFraction);
      ticking = false;
    });
  }

  function initObserver() {
    const sections = document.querySelectorAll('.scroll-section__inner');
    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(e) {
        if (!e.isIntersecting) return;
        e.target.classList.add('visible');
        if (_config.onReveal) _config.onReveal(e.target);
      });
    }, { threshold: 0.15, rootMargin: '-5% 0px' });
    sections.forEach(function(s) { observer.observe(s); });
  }
})();
