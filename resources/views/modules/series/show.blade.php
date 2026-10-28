@extends('app')

@section('contenido')

<main id="main">

  <!-- ========================= TITLE HERO ========================= -->
  <section class="title-hero" aria-labelledby="title-name">
    <div class="title-hero__media">
      <img src="img/backdrop.jpg" alt="Rain-slick city street at night, backdrop from Nightfall Boulevard" width="1600" height="900">
    </div>
    <div class="title-hero__scrim" aria-hidden="true"></div>

    <div class="wrap title-hero__inner">
      <p class="title-hero__crumbs"><a href="index.html">Home</a> <span class="dot-sep">›</span> <a href="index.html#trending">Series</a> <span class="dot-sep">›</span> Nightfall Boulevard</p>
      <p class="hero__badge">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.9 6.26L22 9.27l-5 4.87L18.18 21 12 17.27 5.82 21 7 14.14l-5-4.87 7.1-1.01L12 2Z"/></svg>
        Streamline Original Series
      </p>
      <h1 id="title-name">Nightfall Boulevard</h1>
      <div class="title-hero__meta">
        <span class="score">98% match</span>
        <span class="dot-sep">•</span>
        <span>2026</span>
        <span class="dot-sep">•</span>
        <span class="chip chip--rating">TV-MA</span>
        <span class="dot-sep">•</span>
        <span>4 Seasons</span>
        <span class="dot-sep">•</span>
        <span>32 Episodes</span>
        <span class="dot-sep">•</span>
        <span class="chip chip--gold">4K · Dolby Vision</span>
      </div>
      <div class="title-hero__genres">
        <span class="chip">Neo-noir</span>
        <span class="chip">Thriller</span>
        <span class="chip">Mystery</span>
        <span class="chip">Crime</span>
      </div>
      <div class="title-hero__actions">
        <a class="btn-cta btn-primary" href="#episodes">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
          Play S1:E1
        </a>
        <button class="icon-pill" type="button" aria-label="Add to My List">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </button>
        <button class="icon-pill" type="button" aria-label="Rate this title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11v9H4a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1h3Zm0 0 4.5-8a2 2 0 0 1 2 1.2l.5 4.8H20a2 2 0 0 1 2 2.3l-1.2 6A2 2 0 0 1 18.8 20H7"/></svg>
        </button>
        <button class="icon-pill" type="button" aria-label="Share">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="m8.2 10.8 7.6-3.6M8.2 13.2l7.6 3.6"/></svg>
        </button>
      </div>
    </div>
  </section>

  <!-- ========================= SYNOPSIS + FACTS ========================= -->
  <section class="section section--tight" aria-labelledby="about-title">
    <div class="wrap">
      <div class="title-body">
        <div class="title-body__synopsis reveal">
          <h2 id="about-title">About the show</h2>
          <p>When a rolling blackout drops the coastal city of Marrow Bay into darkness, Detective Ø Halloran is the only officer who saw the last flicker of the grid — and the only one who can't trust his own memory to describe it. Diagnosed with early anterograde amnesia, he has one long night, a pocket recorder, and a boulevard full of witnesses who each remember a different version of the truth.</p>
          <p>Across four seasons, <b>Nightfall Boulevard</b> peels back a single city block at a time — a shuttered cinema, a jazz bar that never closes, a rooftop that shouldn't be reachable — until the smallest lie turns out to be the one that keeps the lights off. Equal parts memory puzzle and mood piece, it's the show critics called "a city painted entirely in the blue hour."</p>
          <h2 style="margin-top:2rem;">Cast &amp; crew</h2>
          <div class="cast-row" role="list" aria-label="Cast">
            <div class="cast-member" role="listitem">
              <img src="img/cast1.jpg" alt="Portrait of Mara Vesely">
              <b>Mara Vesely</b><span>Det. Ø Halloran</span>
            </div>
            <div class="cast-member" role="listitem">
              <img src="img/cast2.jpg" alt="Portrait of Ivo Kade">
              <b>Ivo Kade</b><span>Sol Marchetti</span>
            </div>
            <div class="cast-member" role="listitem">
              <img src="img/cast3.jpg" alt="Portrait of Lena Osei">
              <b>Lena Osei</b><span>The Projectionist</span>
            </div>
            <div class="cast-member" role="listitem">
              <img src="img/cast4.jpg" alt="Portrait of Tomas Reihl">
              <b>Tomas Reihl</b><span>Capt. Doyle</span>
            </div>
            <div class="cast-member" role="listitem">
              <img src="img/cast5.jpg" alt="Portrait of Nadia Brandt">
              <b>Nadia Brandt</b><span>Wren</span>
            </div>
          </div>
        </div>

        <aside class="title-facts reveal" data-delay="1" aria-label="Title details">
          <dl>
            <div><dt>Creator</dt><dd>Selby Warrick</dd></div>
            <div><dt>Starring</dt><dd>Mara Vesely, Ivo Kade, Lena Osei</dd></div>
            <div><dt>Genres</dt><dd><a href="index.html#genres">Thriller</a>, <a href="index.html#genres">Mystery</a>, Crime</dd></div>
            <div><dt>First aired</dt><dd>March 2026</dd></div>
            <div><dt>Seasons</dt><dd>4 seasons · 32 episodes</dd></div>
            <div><dt>Audio</dt><dd>Dolby Atmos · 6 languages</dd></div>
            <div><dt>Subtitles</dt><dd>English, Español, Français +21</dd></div>
            <div><dt>Maturity</dt><dd>TV-MA — violence, language</dd></div>
          </dl>
        </aside>
      </div>
    </div>
  </section>

  <!-- ========================= EPISODES ========================= -->
  <section class="section section--tight" id="episodes" aria-labelledby="episodes-title">
    <div class="wrap">
      <div class="section-head reveal">
        <div>
          <p class="eyebrow">Episodes</p>
          <h2 id="episodes-title">Watch the series</h2>
        </div>
      </div>

      <div class="season-tabs reveal" role="tablist" aria-label="Choose season">
        <button type="button" data-season="1" class="is-active" role="tab" aria-selected="true">Season 1</button>
        <button type="button" data-season="2" role="tab" aria-selected="false">Season 2</button>
        <button type="button" data-season="3" role="tab" aria-selected="false">Season 3</button>
        <button type="button" data-season="4" role="tab" aria-selected="false">Season 4</button>
      </div>

      <!-- Season 1 -->
      <div class="ep-list ep-panel reveal" data-panel="1">
        <article class="episode">
          <div class="episode__no">1</div>
          <div class="episode__thumb">
            <img src="img/ep1.jpg" alt="Still from episode: The Last Flicker" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play The Last Flicker"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>The Last Flicker <small>52 min</small></h3>
            <p>The grid dies at 9:14pm and Halloran starts recording everything he might forget. A shopkeeper swears the lights never came on at all.</p>
          </div>
        </article>

        <article class="episode">
          <div class="episode__no">2</div>
          <div class="episode__thumb">
            <img src="img/ep2.jpg" alt="Still from episode: Blue Hour" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Blue Hour"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Blue Hour <small>48 min</small></h3>
            <p>A jazz bar that runs on a private generator becomes the only lit room on the boulevard — and the last place the missing engineer was seen alive.</p>
          </div>
        </article>

        <article class="episode">
          <div class="episode__no">3</div>
          <div class="episode__thumb">
            <img src="img/ep3.jpg" alt="Still from episode: Reel to Reel" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Reel to Reel"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Reel to Reel <small>55 min</small></h3>
            <p>Inside a shuttered cinema, the projectionist plays Halloran a film of tonight that ends before the night does. Someone spliced the future in.</p>
          </div>
        </article>
      </div>

      <!-- Season 2 -->
      <div class="ep-list ep-panel" data-panel="2" hidden>
        <article class="episode">
          <div class="episode__no">1</div>
          <div class="episode__thumb">
            <img src="img/ep2.jpg" alt="Still from episode: Second Shift" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Second Shift"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Second Shift <small>50 min</small></h3>
            <p>One year on, the blackout repeats to the minute. This time Halloran keeps his notebook — and it's already filled in by his own hand.</p>
          </div>
        </article>
        <article class="episode">
          <div class="episode__no">2</div>
          <div class="episode__thumb">
            <img src="img/ep1.jpg" alt="Still from episode: The Understudy" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play The Understudy"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>The Understudy <small>47 min</small></h3>
            <p>Wren takes the case Halloran can't hold onto, and discovers the witnesses have started rehearsing their statements like lines.</p>
          </div>
        </article>
      </div>

      <!-- Season 3 -->
      <div class="ep-list ep-panel" data-panel="3" hidden>
        <article class="episode">
          <div class="episode__no">1</div>
          <div class="episode__thumb">
            <img src="img/ep3.jpg" alt="Still from episode: Grid North" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Grid North"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Grid North <small>53 min</small></h3>
            <p>The blackout spreads uptown and a rival precinct wants the recorder. Halloran has to decide which of his memories are worth keeping secret.</p>
          </div>
        </article>
        <article class="episode">
          <div class="episode__no">2</div>
          <div class="episode__thumb">
            <img src="img/ep2.jpg" alt="Still from episode: Rooftop People" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Rooftop People"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Rooftop People <small>49 min</small></h3>
            <p>There's a rooftop no stairwell reaches, and a crowd already up there waiting. The projectionist knew all their names.</p>
          </div>
        </article>
      </div>

      <!-- Season 4 -->
      <div class="ep-list ep-panel" data-panel="4" hidden>
        <article class="episode">
          <div class="episode__no">1</div>
          <div class="episode__thumb">
            <img src="img/ep1.jpg" alt="Still from episode: Daybreak, Maybe" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play Daybreak, Maybe"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>Daybreak, Maybe <small>58 min</small></h3>
            <p>The final night. Every witness gathers on the boulevard for a sunrise that the film says will never come, and Halloran finally plays his own tape back.</p>
          </div>
        </article>
        <article class="episode">
          <div class="episode__no">2</div>
          <div class="episode__thumb">
            <img src="img/ep3.jpg" alt="Still from episode: The Long Boulevard" width="480" height="270" loading="lazy">
            <button type="button" aria-label="Play The Long Boulevard"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
          </div>
          <div class="episode__info">
            <h3>The Long Boulevard <small>61 min</small></h3>
            <p>Series finale. The lights come back on — and reveal a city that remembers the night perfectly, and one detective who chose to forget it.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ========================= MORE LIKE THIS ========================= -->
  <section class="section section--tight" aria-labelledby="more-title">
    <div class="wrap">
      <div class="section-head reveal">
        <div>
          <p class="eyebrow">If you liked this</p>
          <h2 id="more-title">More like this</h2>
        </div>
        <div class="scroll-ctrls" aria-hidden="true">
          <button class="icon-btn" type="button" data-scroll="prev" data-target="moreRow" aria-label="Scroll left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
          </button>
          <button class="icon-btn" type="button" data-scroll="next" data-target="moreRow" aria-label="Scroll right">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
          </button>
        </div>
      </div>
    </div>
    <div class="wrap">
      <div class="snap-row" id="moreRow" tabindex="0" role="list" aria-label="Similar titles">
        <article class="rank-card" role="listitem">
          <div class="rank-card__body" style="margin-left:0;">
            <div class="rank-card__poster">
              <img src="img/t3.jpg" alt="Poster for Projectionist" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Projectionist"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Projectionist</h3><p>Mystery · Series</p></div>
          </div>
        </article>
        <article class="rank-card" role="listitem">
          <div class="rank-card__body" style="margin-left:0;">
            <div class="rank-card__poster">
              <img src="img/t5.jpg" alt="Poster for After Hours City" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play After Hours City"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>After Hours City</h3><p>Crime · Series</p></div>
          </div>
        </article>
        <article class="rank-card" role="listitem">
          <div class="rank-card__body" style="margin-left:0;">
            <div class="rank-card__poster">
              <img src="img/t2.jpg" alt="Poster for The Long Reel" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play The Long Reel"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>The Long Reel</h3><p>Drama · Film</p></div>
          </div>
        </article>
        <article class="rank-card" role="listitem">
          <div class="rank-card__body" style="margin-left:0;">
            <div class="rank-card__poster">
              <img src="img/t1.jpg" alt="Poster for Marrow Bay" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Marrow Bay"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Marrow Bay</h3><p>Thriller · Series</p></div>
          </div>
        </article>
        <article class="rank-card" role="listitem">
          <div class="rank-card__body" style="margin-left:0;">
            <div class="rank-card__poster">
              <img src="img/t6.jpg" alt="Poster for Silver Screen Diaries" width="500" height="750" loading="lazy">
              <button class="rank-card__play" type="button" aria-label="Play Silver Screen Diaries"><span><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span></button>
            </div>
            <div class="rank-card__meta"><h3>Silver Screen Diaries</h3><p>Documentary · Film</p></div>
          </div>
        </article>
      </div>
    </div>
  </section>

</main>
   
@endsection