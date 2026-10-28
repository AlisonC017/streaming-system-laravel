@extends('app')

@section('contenido')
    <!-- ========================= HERO ========================= -->
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero__media">
      <img src="img/hero.jpg" alt="Neon-lit city skyline at night from the series Nightfall Boulevard" width="1600" height="900">
    </div>
    <div class="hero__scrim" aria-hidden="true"></div>

    <div class="wrap hero__inner">
      <p class="hero__badge">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
        Streamline Original Series
      </p>
      <h1 id="hero-title">Nightfall Boulevard</h1>
      <div class="hero__meta">
        <span class="score">98% match</span>
        <span class="dot-sep">•</span>
        <span>2026</span>
        <span class="dot-sep">•</span>
        <span class="chip chip--rating">TV-MA</span>
        <span class="dot-sep">•</span>
        <span>4 Seasons</span>
        <span class="dot-sep">•</span>
        <span>Neo-noir Thriller</span>
        <span class="dot-sep">•</span>
        <span class="chip chip--gold">4K · HDR</span>
      </div>
      <p class="hero__synopsis">A blackout swallows the coast and a detective with a failing memory has one long night to trace the last person who saw the lights go out. Every block hides a witness. Every witness is lying.</p>
      <div class="hero__actions">
        <a class="btn-cta btn-primary" href="{{ asset('') }}">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
          Play S1:E1
        </a>
        <a class="btn-cta btn-glass" href="title.html">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9.5"/><path d="M12 11v5"/><path d="M12 7.6h.01"/></svg>
          More info
        </a>
      </div>
      <p class="hero__note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
        7 days free, then plans from $6.99/mo — cancel anytime.
      </p>
    </div>

    <div class="hero__cue" aria-hidden="true">
      Scroll
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
    </div>
  </section>

  <!-- ========================= TRENDING ROW ========================= -->
  <section class="section" id="trending" aria-labelledby="trending-title">
    <div class="wrap">
      <div class="section-head reveal">
        <div>
          <p class="eyebrow">Top 10 this week</p>
          <h2 id="trending-title">Trending now</h2>
        </div>
        <div class="scroll-ctrls" aria-hidden="true">
          <button class="icon-btn" type="button" data-scroll="prev" data-target="trendingRow" aria-label="Scroll trending left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
          </button>
          <button class="icon-btn" type="button" data-scroll="next" data-target="trendingRow" aria-label="Scroll trending right">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
          </button>
        </div>
      </div>
    </div>

    <div class="wrap">
      <div class="snap-row" id="trendingRow" tabindex="0" role="list" aria-label="Trending titles">
        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">1</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <span class="rank-card__flag">New</span>
              <img src="img/t1.jpg" alt="Poster for Nightfall Boulevard" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Nightfall Boulevard"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Nightfall Boulevard</h3><p>Thriller · Series</p></div>
          </div>
        </article>

        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">2</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <img src="img/t2.jpg" alt="Poster for The Long Reel" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play The Long Reel"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>The Long Reel</h3><p>Drama · Film</p></div>
          </div>
        </article>

        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">3</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <span class="rank-card__flag rank-card__flag--gold">Award</span>
              <img src="img/t3.jpg" alt="Poster for Projectionist" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Projectionist"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Projectionist</h3><p>Mystery · Series</p></div>
          </div>
        </article>

        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">4</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <img src="img/t4.jpg" alt="Poster for Encore: Live from Harbor Hall" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Encore: Live from Harbor Hall"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Encore: Harbor Hall</h3><p>Concert · Live</p></div>
          </div>
        </article>

        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">5</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <img src="img/t5.jpg" alt="Poster for After Hours City" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play After Hours City"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>After Hours City</h3><p>Crime · Series</p></div>
          </div>
        </article>

        <article class="rank-card" role="listitem">
          <span class="rank-card__num" aria-hidden="true">6</span>
          <div class="rank-card__body">
            <div class="rank-card__poster">
              <span class="rank-card__flag">New</span>
              <img src="img/t6.jpg" alt="Poster for Silver Screen Diaries" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Silver Screen Diaries"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Silver Screen Diaries</h3><p>Documentary · Film</p></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ========================= GENRES ========================= -->
  <section class="section section--tight" id="genres" aria-labelledby="genres-title">
    <div class="wrap">
      <div class="section-head reveal">
        <div>
          <p class="eyebrow">Something for every night</p>
          <h2 id="genres-title">Browse by genre</h2>
        </div>
        <p>From slow-burn documentaries to Friday-night blockbusters — 12,000+ titles, sorted the way you actually watch.</p>
      </div>

      <div class="genre-grid reveal">
        <a class="genre-tile tile-thriller" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Thrillers</h3><span>1,240 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-doc" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Documentary</h3><span>860 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-comedy" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Comedy</h3><span>1,510 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-scifi" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v3M12 18v3M4.2 7l2.6 1.5M17.2 15.5l2.6 1.5M19.8 7l-2.6 1.5M6.8 15.5 4.2 17M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Sci-Fi</h3><span>720 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-drama" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h7v14H5a1 1 0 0 1-1-1z"/><path d="M13 5h7v13a1 1 0 0 1-1 1h-6z"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Drama</h3><span>2,030 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-kids" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21c1.2-3.5 4-5 7-5s5.8 1.5 7 5"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Kids &amp; Family</h3><span>640 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-concert" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l10-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="16" cy="16" r="3"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Concerts</h3><span>310 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
        <a class="genre-tile tile-anime" href="#trending">
          <svg class="genre-tile__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3c5 0 9 3.6 9 8 0 2.4-1.4 4.2-3 5l1 4-4-2.2c-.9.2-2 .2-3 .2-5 0-9-3.6-9-7.2S7 3 12 3Z"/></svg>
          <div class="genre-tile__bottom">
            <div><h3>Anime</h3><span>480 titles</span></div>
            <span class="genre-tile__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- ========================= SPOTLIGHT ========================= -->
  <section class="section" id="spotlight" aria-labelledby="spotlight-title">
    <div class="wrap">
      <div class="spotlight">
        <figure class="spotlight__figure reveal">
          <img src="img/spotlight.jpg" alt="Still from the film Projectionist showing a lone figure in an empty theater" width="1000" height="1200">
          <span class="chip chip--gold spotlight__ep">Limited Series</span>
          <button class="spotlight__play" type="button" aria-label="Play trailer for Projectionist">
            <span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span>
            Watch trailer
          </button>
        </figure>

        <div class="spotlight__body reveal" data-delay="1">
          <p class="eyebrow">Editors' pick of the month</p>
          <h2 id="spotlight-title">Projectionist</h2>
          <div class="spotlight__stats">
            <span class="chip chip--gold">96% match</span>
            <span class="chip">2026</span>
            <span class="chip chip--rating">TV-MA</span>
            <span class="chip">6 Episodes</span>
          </div>
          <p>The last projectionist of a dying downtown cinema starts finding messages spliced into the reels — frames that shouldn't exist, showing a version of tonight that hasn't happened yet. A slow-burn mystery about memory, film, and the stories we run on repeat.</p>
          <div class="spotlight__cast">
            <div class="cast-stack" aria-hidden="true">
              <img src="img/cast1.jpg" alt="">
              <img src="img/cast2.jpg" alt="">
              <img src="img/cast3.jpg" alt="">
              <img src="img/cast4.jpg" alt="">
            </div>
            <small>Starring <b>Mara Vesely</b>, <b>Ivo Kade</b> &amp; <b>Lena Osei</b></small>
          </div>
          <div class="spotlight__actions">
            <a class="btn-cta btn-primary" href="title.html">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
              Watch now
            </a>
            <button class="btn-cta btn-ghost" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              Add to My List
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================= DEVICES ========================= -->
  <section class="section devices" id="devices" aria-labelledby="devices-title">
    <div class="wrap">
      <div class="devices__head reveal">
        <p class="eyebrow" style="justify-content:center;">One membership, every screen</p>
        <h2 id="devices-title">Watch on any device</h2>
        <p>Start on the train, finish on the couch. Streamline picks up exactly where you left off across up to six devices.</p>
      </div>

      <div class="device-grid reveal">
        <div class="device-card">
          <span class="device-card__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></span>
          <h3>Smart TV</h3>
          <p>4K HDR on every major TV OS</p>
        </div>
        <div class="device-card">
          <span class="device-card__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></svg></span>
          <h3>Phone</h3>
          <p>iOS &amp; Android, with offline saves</p>
        </div>
        <div class="device-card">
          <span class="device-card__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M2 21h20"/></svg></span>
          <h3>Laptop</h3>
          <p>Any modern browser, no plugins</p>
        </div>
        <div class="device-card">
          <span class="device-card__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 18h6"/></svg></span>
          <h3>Tablet</h3>
          <p>Picture-in-picture on the go</p>
        </div>
        <div class="device-card">
          <span class="device-card__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12a3 3 0 0 1 3 3l1 6a2.5 2.5 0 0 1-4.6 1.4L16 16H8l-2.4 2.4A2.5 2.5 0 0 1 1 17l1-6a3 3 0 0 1 3-3Z"/><path d="M8 12h.01M6 10v4"/></svg></span>
          <h3>Console</h3>
          <p>PlayStation &amp; Xbox apps</p>
        </div>
      </div>

      <p class="devices__foot reveal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v11M8 10l4 4 4-4M5 20h14"/></svg>
        Download &amp; go — save episodes for flights, tunnels and dead zones on Standard and Premium.
      </p>
    </div>
  </section>

  <!-- ========================= PRICING ========================= -->
  <section class="section" id="pricing" aria-labelledby="pricing-title">
    <div class="wrap">
      <div class="pricing__top reveal">
        <p class="eyebrow" style="justify-content:center;">Simple, honest pricing</p>
        <h2 id="pricing-title">Pick a plan, change it anytime</h2>
        <p>No contracts, no ads on Premium, no surprise fees. Every plan starts with a 7-day free trial.</p>
        <div class="billing-toggle" role="group" aria-label="Choose billing period">
          <button type="button" id="billMonthly" class="is-active" aria-pressed="true">Monthly</button>
          <button type="button" id="billAnnual" aria-pressed="false">Annual <span class="save">−20%</span></button>
        </div>
      </div>

      <div class="plan-grid reveal">
        <!-- Basic -->
        <div class="plan">
          <p class="plan__name">Basic</p>
          <p class="plan__blurb">Great for solo, on-the-go viewing.</p>
          <div class="plan__price">
            <span class="amount" data-monthly="$6.99" data-annual="$5.59">$6.99</span>
            <span class="per">/mo</span>
          </div>
          <p class="plan__billed" data-monthly="Billed monthly" data-annual="$67/yr — save $17">Billed monthly</p>
          <ul class="plan__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Full library, 12,000+ titles</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Full HD (1080p)</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> 1 screen at a time</li>
            <li class="off"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg> No offline downloads</li>
            <li class="off"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg> Includes light ads</li>
          </ul>
          <a class="btn-cta btn-ghost btn-block" href="#">Start free trial</a>
        </div>

        <!-- Standard (featured) -->
        <div class="plan plan--featured">
          <span class="plan__ribbon">Most popular</span>
          <p class="plan__name">Standard</p>
          <p class="plan__blurb">The sweet spot for couples &amp; roommates.</p>
          <div class="plan__price">
            <span class="amount" data-monthly="$12.99" data-annual="$10.39">$12.99</span>
            <span class="per">/mo</span>
          </div>
          <p class="plan__billed" data-monthly="Billed monthly" data-annual="$124/yr — save $32">Billed monthly</p>
          <ul class="plan__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Everything in Basic, ad-free</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> 4K Ultra HD + HDR10</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> 2 screens at a time</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Offline downloads on 2 devices</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Early access to Originals</li>
          </ul>
          <a class="btn-cta btn-primary btn-block" href="#">Start free trial</a>
        </div>

        <!-- Premium -->
        <div class="plan">
          <p class="plan__name">Premium</p>
          <p class="plan__blurb">Best for households &amp; cinephiles.</p>
          <div class="plan__price">
            <span class="amount" data-monthly="$17.99" data-annual="$14.39">$17.99</span>
            <span class="per">/mo</span>
          </div>
          <p class="plan__billed" data-monthly="Billed monthly" data-annual="$172/yr — save $44">Billed monthly</p>
          <ul class="plan__list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Everything in Standard</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> 4K + Dolby Vision &amp; Atmos</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> 6 screens &amp; 6 profiles</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Offline downloads on 6 devices</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg> Live concerts &amp; premieres</li>
          </ul>
          <a class="btn-cta btn-ghost btn-block" href="#">Start free trial</a>
        </div>
      </div>
      <p class="plan__foot-note">Prices in USD. Cancel online in two clicks — no phone calls, no retention gauntlet.</p>
    </div>
  </section>

  <!-- ========================= VOICES / STATS ========================= -->
  <section class="section voices" id="voices" aria-labelledby="voices-title">
    <div class="wrap">
      <div class="stats-strip reveal">
        <div class="stat"><b>12,400+</b><span>Titles &amp; growing</span></div>
        <div class="stat"><b>40+</b><span>Award-winning Originals</span></div>
        <div class="stat"><b>4K HDR</b><span>On Standard &amp; up</span></div>
        <div class="stat"><b>190</b><span>Countries streaming</span></div>
      </div>

      <div class="section-head reveal">
        <div>
          <p class="eyebrow">Loved by night owls</p>
          <h2 id="voices-title">Where the good nights go</h2>
        </div>
      </div>

      <div class="voice-grid reveal">
        <figure class="voice">
          <div class="voice__stars" aria-label="5 out of 5 stars">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
          </div>
          <p>"The 4K night scenes actually look like night — not a grey smear. Nightfall Boulevard alone was worth the trial."</p>
          <figcaption class="voice__who">
            <img src="img/cast5.jpg" alt="Portrait of subscriber Dana R.">
            <span><b>Dana R.</b><span>Premium subscriber, 2 yrs</span></span>
          </figcaption>
        </figure>

        <figure class="voice">
          <div class="voice__stars" aria-label="5 out of 5 stars">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
          </div>
          <p>"Downloads that just work. I watched three episodes on a transatlantic flight without a single buffer or login prompt."</p>
          <figcaption class="voice__who">
            <img src="img/cast3.jpg" alt="Portrait of subscriber Malik T.">
            <span><b>Malik T.</b><span>Standard subscriber, 1 yr</span></span>
          </figcaption>
        </figure>

        <figure class="voice">
          <div class="voice__stars" aria-label="5 out of 5 stars">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
          </div>
          <p>"The live concert stream from Harbor Hall felt like front-row seats. Zero lag, real Atmos. Cancelled my other two services."</p>
          <figcaption class="voice__who">
            <img src="img/cast2.jpg" alt="Portrait of subscriber Priya S.">
            <span><b>Priya S.</b><span>Premium subscriber, 3 yrs</span></span>
          </figcaption>
        </figure>
      </div>
    </div>
  </section>

  <!-- ========================= FAQ ========================= -->
  <section class="section" id="faq" aria-labelledby="faq-title">
    <div class="wrap">
      <div class="faq-wrap">
        <div class="faq-aside reveal">
          <p class="eyebrow">Good to know</p>
          <h2 id="faq-title">Frequently asked questions</h2>
          <p>Everything about plans, devices and your free week. Still stuck? Our support team answers in under 5 minutes, day or night.</p>
          <a class="btn-cta btn-ghost" href="#">
            Contact support
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>

        <div class="accordion reveal" data-delay="1" id="faqAccordion">
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq1h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq1" aria-expanded="false" aria-controls="fq1">How does the 7-day free trial work?</button>
            </h3>
            <div id="fq1" class="accordion-collapse collapse" aria-labelledby="fq1h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">Pick any plan and watch everything free for 7 days. We'll remind you two days before it ends, and you can cancel in two clicks — you're never charged if you leave during the trial.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq2h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq2" aria-expanded="false" aria-controls="fq2">Can I change or cancel my plan anytime?</button>
            </h3>
            <div id="fq2" class="accordion-collapse collapse" aria-labelledby="fq2h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">Yes. Upgrade, downgrade or cancel from your account page whenever you like. Plan changes take effect on your next billing date, and there are no cancellation fees — ever.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq3h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq3" aria-expanded="false" aria-controls="fq3">How many people can watch at once?</button>
            </h3>
            <div id="fq3" class="accordion-collapse collapse" aria-labelledby="fq3h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">Basic supports one screen, Standard two, and Premium up to six simultaneous streams with six separate profiles — perfect for a full household with very different taste.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq4h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq4" aria-expanded="false" aria-controls="fq4">Can I download titles to watch offline?</button>
            </h3>
            <div id="fq4" class="accordion-collapse collapse" aria-labelledby="fq4h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">Standard and Premium include offline downloads on the mobile and tablet apps — save whole seasons for flights, commutes and dead zones, then delete them with a tap when you're done.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq5h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq5" aria-expanded="false" aria-controls="fq5">Is 4K and Dolby Atmos really included?</button>
            </h3>
            <div id="fq5" class="accordion-collapse collapse" aria-labelledby="fq5h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">On supported titles and devices, yes — 4K HDR10 comes with Standard, and Dolby Vision plus Dolby Atmos surround sound are included on Premium at no extra charge.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h3 class="accordion-header" id="fq6h">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fq6" aria-expanded="false" aria-controls="fq6">What can I watch on Streamline?</button>
            </h3>
            <div id="fq6" class="accordion-collapse collapse" aria-labelledby="fq6h" data-bs-parent="#faqAccordion">
              <div class="accordion-body">A 12,400-title library of films, series, documentaries, kids' shows and live concerts — plus 40+ Streamline Originals you won't find anywhere else, with new releases every Friday.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection