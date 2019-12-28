<script>
  import {
    faBriefcase,
    faEnvelope,
    faChevronDown
  } from "@fortawesome/free-solid-svg-icons";
  import { faGithub, faLinkedin } from "@fortawesome/free-brands-svg-icons";

  import Icon from "../components/Icon.svelte";
</script>

<style>
  .hero {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: var(--primary);
  }

  h1 {
    color: var(--secondary);
    text-shadow: 0px 2px 3px darkgrey;
    font-family: "Roboto Condensed", sans-serif;
  }

  .icons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
  }

  :global(.down) {
    position: absolute;
    bottom: 5%;
    left: 50%;
    right: 50%;
    animation: bounce 4s ease-in-out infinite;
  }

  :global(.down) :global(svg) {
    transform: translateX(-50%);
  }
</style>

<div class="hero">
  <h1>zach hardesty</h1>
  <div class="icons">
    <Icon
      data={faEnvelope}
      inverse
      title="email"
      label="Email"
      link="mailto:hello@zachhardesty.com" />
    <Icon
      data={faGithub}
      inverse
      title="github"
      label="Github"
      link="https://github.com/zachhardesty7" />
    <Icon
      data={faLinkedin}
      inverse
      title="linkedIn"
      label="LinkedIn"
      link="https://www.linkedin.com/in/zachhardesty7" />
    <Icon
      data={faBriefcase}
      inverse
      title="resume"
      label="Resume"
      link="https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing" />
  </div>
  <Icon
    class="down"
    data={faChevronDown}
    inverse
    link="#about"
    title="next page" />
</div>
