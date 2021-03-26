<script>
  import { Experience, Footer, Hero, Icons, Portfolio } from "../components";
  import { about, data, icons, skills } from "../data";
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
    <Icons {icons} />
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

  #skills {
    padding-top: 2rem;
  }

  #skills div {
    text-align: left;
  }

  #skills div p {
    margin-top: 0.3em;
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
</style>
