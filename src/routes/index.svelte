<script>
  import { Experience, Footer, Hero, Icons, Portfolio } from "../components";
  import { data, icons, skills } from "../data";
</script>

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

<svelte:head>
  <title>Zach Hardesty Web Portfolio</title>
  <style>
    @import "~sanitize.css";
    @import "~sanitize.css/forms.css";
    @import "~sanitize.css/typography.css";

    :root {
      --primary: #0b3c5d;
      --secondary: #fafafa;
      --accent: #d9b310;
      --dark: #2c2c34;
      --light: #cccccc;
      --text: rgba(0, 0, 0, 0.87);
    }

    *,
    :after,
    :before {
      box-sizing: inherit;
    }

    html,
    body {
      height: 100%;
    }

    html {
      /* NOTE: when moving this from 14px -> 18px, I updated most `rem` values to be 16/18 of their previous size to average between maintaining and adjusting since body font size was the main problem. this kinda presumes I nailed spacing the first time... */
      font-size: 18px;
      box-sizing: border-box;
      line-height: 1.5;
      font-family: "Roboto", sans-serif;
      font-weight: normal;
      color: var(--text);
      scroll-padding-top: 60px; height of sticky header
    }

    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      min-width: 320px;
      font-size: 18px;
      line-height: 1.4285em;
      color: var(--text);
      font-family: "Roboto", "Helvetica Neue", Arial, Helvetica, sans-serif;
    }

    body::selection {
      background-color: var(--accent);
      color: #ffffff;
    }

    li,
    ul {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }

    a {
      text-decoration: none;
      transition: color 0.3s ease;
    }

    a,
    .link,
    button {
      cursor: pointer;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      font-weight: 400;
      line-height: 1.1;
      margin: 0;
      padding: 0;
    }

    h1 {
      font-size: 3.73rem;
      line-height: 110%;
      margin: 1.87rem 0 1.49rem 0;
    }
    h2 {
      font-size: 3.17rem;
      line-height: 110%;
      margin: 1.58rem 0 1.27rem 0;
      color: var(--primary);
      position: relative;
      margin-right: auto;
      padding-bottom: 1.42rem;
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
    h3 {
      color: var(--primary);
      font-size: 2.31rem;
      line-height: 110%;
      margin: 1.3rem 0 1.04rem 0;
    }

    h4 {
      font-size: 2.03rem;
      line-height: 110%;
      margin: 1.01rem 0 0.81rem 0;
    }

    h5 {
      font-size: 1.46rem;
      line-height: 110%;
      margin: 0.73rem 0 0.58rem 0;
    }

    h6 {
      font-size: 0.89rem;
      line-height: 110%;
      margin: 0.44rem 0 0.36rem 0;
    }

    button {
      border: none;
      color: var(--primary);
      transition: border 0.4s;
      font-size: 1.11rem;
      margin: 0 10px;
      padding: 10px 0;
      border-bottom: 0px solid var(--accent);
    }
    button.active {
      border-bottom-width: 2px;
    }
    button:hover {
      border-bottom-width: 5px;
    }
    button:focus {
      outline: none;
    }

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
</svelte:head>
