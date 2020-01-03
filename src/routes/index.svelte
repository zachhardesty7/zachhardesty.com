<script>
  import { Experience, Hero, Icons, Icon, Portfolio } from "../components";
  import { apps, data, experiences, icons, skills, websites } from "../data";
</script>

<style>
  h2 {
    color: var(--primary);
    position: relative;
    margin-right: auto;
    padding-bottom: 1.6rem;
  }

  h2::before {
    content: "";
    position: absolute;
    bottom: 8px;
    left: -20px;
    height: 55%;
    width: 100%;
    z-index: -1;
    background-color: var(--accent);
  }

  .page {
    width: 70%;
    margin: auto;
  }

  section {
    width: 60%;
    margin: auto;
    display: flex;
    flex-direction: column;
    padding-top: 1rem;
    margin-top: 2rem;
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
</style>

<svelte:head>
  <title>Zach Hardesty Web Portfolio</title>
</svelte:head>

<Hero />
<div class="page">
  <section id="about">
    <h2>About Me</h2>
    <div>
      <img class="portrait" alt="portrait" src="/portrait.jpg" />
    </div>

    <p>
      {`First, allow me to tell you my story. I am now enrolled in the University of Texas at Austin's computer science degree plan but my journey began much earlier. From a young age, understanding the “why” and the “how” of everything from common items to modern marvels fascinated me. Naturally, once I received my parents' old computer I began tinkering and familiarizing myself. Learning to program daunted me but I hesitantly began exploring the basics of HTML, CSS, and JavaScript in my spare time. After getting a job with my neighbor, I started tinkering around in WordPress. There, I uncovered my fascination with web development and the rest is history.`}
    </p>
    <p>
      {`What does the future entail for me? Cryptocurrency, machine learning, and web and mobile app development all pique my interests. That being said, I find it difficult to commit to a particular path this early and stay open to all possibilities. Please do not to hesitate to contact me with opportunities or inquiries and connect with me on LinkedIn!`}
    </p>
    <div class="skills">
      <b>Proficient with:</b>
      <p>{skills.proficient.join(', ')}</p>
      <b>Familiar with:</b>
      <p>{skills.familiar.join(', ')}</p>
    </div>
  </section>
  <section>
    <Icons {icons} />
  </section>
  <section>
    <h2>Experience</h2>
    <Experience data={experiences} />
  </section>
  <section>
    <h2>Projects</h2>
    <Portfolio items={data} />
  </section>
</div>
<!-- <Footer /> -->
