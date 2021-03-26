<script>
  import { Experience, Footer, Hero, Icons, Portfolio } from "../components";
  import { data, icons, skills } from "../data";
</script>

<svelte:head>
  <title>Zach Hardesty Web Portfolio</title>
</svelte:head>

<Hero />
<div class="page">
  <section id="about" class="container">
    <h2>About Me</h2>
    <div><img class="portrait" alt="portrait" src="/portrait.jpg" /></div>

    <p>
      {`First, allow me to tell you my story. I am now enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a young age, understanding the “why” and the “how” of everything from common items to modern marvels fascinated me. Naturally, once I received my parents' old computer I began tinkering and familiarizing myself. Learning to program daunted me but I hesitantly began exploring the basics of HTML, CSS, and JavaScript in my spare time. After getting a job with my neighbor, I started tinkering around in WordPress. There, I uncovered my fascination with web development and the rest is history.`}
    </p>
    <p>
      {`I'm currently a front end website developer, but what does the future entail for
      me? Cryptocurrency/blockchain, machine learning, and mobile app development all
      pique my interests, but I stay open to all possibilities. Please do not hesitate
      to contact me with opportunities or inquiries and connect with me on LinkedIn!`}
    </p>
    <div class="skills">
      <b>Proficient with:</b>
      <p>{skills.proficient.join(", ")}</p>
      <b>Familiar with:</b>
      <p>{skills.familiar.join(", ")}</p>
    </div>
  </section>
  <section>
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
  section.container {
    max-width: 40rem;
    justify-content: center;
  }

  .portrait {
    width: 15rem;
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
