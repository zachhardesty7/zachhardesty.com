<script>
  import { onMount } from "svelte";
  import { Experience, Footer, Hero, Icons, Portfolio } from "../components";
  import { about, data, icons, skills } from "../data";

  let isDarkTheme = false;

  onMount(() => {
    isDarkTheme = window.matchMedia("(prefers-color-scheme: dark)").matches;
  });
</script>

<svelte:head>
  <title>Zach Hardesty Web Portfolio</title>
</svelte:head>

<Hero />
<div class="page">
  <section id="about" class="container">
    <h2>About Me</h2>
    <img class="portrait" alt="portrait" src="/portrait.jpg" />

    <p>{about.background}</p>
    <p>{about.plans}</p>
    <div class="skills">
      <b>Proficient with:</b>
      <p>{skills.proficient.join(", ")}</p>
      <b>Familiar with:</b>
      <p>{skills.familiar.join(", ")}</p>
    </div>
  </section>
  <section class="container">
    <Icons {icons} inverse={isDarkTheme} />
  </section>
  <section id="experience" class="container">
    <h2>Experience</h2>
    <Experience />
  </section>
  <section id="projects">
    <h2>Projects</h2>
    <Portfolio items={data} />
  </section>
</div>
<Footer />

<style>
  @import "../global.css";

  .page {
    max-width: 60rem;
    margin: auto;
  }

  section {
    padding-left: 2rem;
    padding-right: 2rem;
    display: flex;
    flex-direction: column;
    padding-top: 1rem;
    margin-top: 2rem;
    margin: auto;
  }

  section h2 {
    color: var(--primary);
    position: relative;
    margin-right: auto;
    padding-bottom: 1.42rem;
  }

  section h2::before {
    content: "";
    position: absolute;
    bottom: 8px;
    left: -20px;
    height: 55%;
    width: 100%;
    z-index: -1;
    background-color: var(--accent);
  }

  .portrait {
    width: 15rem;
    max-width: 100%;
    border-radius: 100%;
    text-align: center;
  }

  .skills {
    padding-top: 2rem;
    display: block;
    text-align: left;
  }
  .skills p {
    margin-top: 0.3em;
  }

  #about,
  #projects,
  #experience {
    padding-top: 1rem;
    margin-top: 2rem;
  }

  #about > p {
    text-align: left;
  }

  @keyframes bounce {
    0%,
    100% {
      transform: translate(-50%, 0);
    }
    50% {
      transform: translate(-50%, 40%);
    }
  }

  @media (prefers-color-scheme: dark) {
    section h2 {
      color: var(--grey-0);
    }

    section h2::before {
      opacity: 0.8;
    }
  }
</style>
