<script>
  const defaultColors = {
    primary: "#0b3c5d",
    secondary: "#fafafa",
    accent: "#d9b310",
    dark: "#2c2c34",
    light: "#cccccc"
  };

  import apps from "../data/apps.json";
  import data from "../data/data.json";
  import experiences from "../data/experiences.json";
  import skills from "../data/skills.json";
  import websites from "../data/websites.json";

  $: display = "apps";
</script>

<style>
  /* h1, */
  /* figure, */
  p {
    text-align: center;
    margin: 0 auto;
  }

  /* h1 {
    font-size: 2.8em;
    text-transform: uppercase;
    font-weight: 700;
    margin: 0 0 0.5em 0;
  } */

  /* figure {
    margin: 0 0 1em 0;
  } */

  img {
    width: 100%;
    max-width: 400px;
    margin: 0 0 1em 0;
  }

  p {
    margin: 1em auto;
  }

  /* @media (min-width: 480px) {
    h1 {
      font-size: 4em;
    }
  } */
</style>

<svelte:head>
  <title>Zach Hardesty Web Portfolio</title>
</svelte:head>

<div>
  <!-- <Header /> -->
  <!-- <Title /> -->
  <main>
    <section>
      <h3>About Me</h3>
      <div>
        <img className="portrait" alt="portrait" src="/portrait.jpg" />
      </div>

      <h4>Welcome to my web portfolio!</h4>
      <p>
        {`First, allow me to tell you my story. I am now enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a young age, understanding the “why” and the “how” of everything from common items to modern marvels fascinated me. Naturally, once I received my parents' old computer I began tinkering and familiarizing myself. Learning to program daunted me but I hesitantly began exploring the basics of HTML, CSS, and JavaScript in my spare time. After getting a job with my neighbor, I started tinkering around in WordPress. There, I uncovered my fascination with web development and the rest is history.`}
      </p>
      <p>
        {`What does the future entail for me? Cryptocurrency, machine learning, and web and mobile app development all pique my interests. That being said, I find it difficult to commit to a particular path this early and stay open to all possibilities. Please do not to hesitate to contact me with opportunities or inquiries and connect with me on LinkedIn!`}
      </p>

    </section>
    <!-- <section
      label
      padded="top"
      color="rgba(0,0,0,.7)"
      colorHover="rgba(0,0,0,1)"
      size="big"
      justify="center">
      <div name="Mail" link="mailto:hello@zachhardesty.com" />
      <div name="Github" link="https://github.com/zachhardesty7" />
      <div name="LinkedIn" link="https://www.linkedin.com/in/zachhardesty7" />
      <div
        name="briefcase"
        label="Resume"
        link="https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing" />
    </section> -->
    <section>
      <h5>Skills</h5>
      <div>
        <b>Proficient with:</b>
        <p>{skills.proficient.join(', ')}</p>
      </div>
      <div>
        <b>Familiar with:</b>
        <p>{skills.familiar.join(', ')}</p>
      </div>
    </section>
    <section>
      <!-- <Experience data={experiences} /> -->

    </section>
    <section>
      <h3>Projects</h3>
      <div>
        <button
          type="button"
          active={display === 'app'}
          on:click={() => (display = 'app')}>
          Apps
        </button>
        <button
          type="button"
          active={display === 'website'}
          on:click={() => (display = 'website')}>
          Websites
        </button>
      </div>
      <!-- <Portfolio items={display === 'website' ? websites : apps} {images} /> -->
      <p>{display}</p>
    </section>
  </main>
  <!-- <Footer /> -->
</div>
