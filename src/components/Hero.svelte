<script>
  import {
    faBriefcase,
    faEnvelope,
    faChevronDown
  } from "@fortawesome/free-solid-svg-icons";
  import { faGithub, faLinkedin } from "@fortawesome/free-brands-svg-icons";
  import Icon from "svelte-awesome";
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

  a {
    opacity: 0.7;
    transition: opacity 0.3s ease;
  }

  a:hover {
    opacity: 1;
    transition: opacity 0.3s ease;
  }

  .icons a {
    padding: 0 0.5em;
  }
  .icons a:first-child {
    padding-left: 0;
  }
  .icons a:last-child {
    padding-right: 0;
  }

  .down {
    position: absolute;
    bottom: 5%;
    left: 50%;
    right: 50%;
    animation: bounce 4s ease-in-out infinite;
  }

  .down :global(svg) {
    transform: translateX(-50%);
  }
</style>

<div class="hero">
  <h1>zach hardesty</h1>
  <div class="icons">
    <a title="email" href="mailto:hello@zachhardesty.com">
      <Icon data={faEnvelope} scale="1.5" inverse label="email" />
      <!-- TODO: add visible icon label -->
    </a>
    <a title="github" href="https://github.com/zachhardesty7">
      <Icon data={faGithub} scale="1.5" inverse label="github" />
    </a>
    <a title="linkedIn" href="https://www.linkedin.com/in/zachhardesty7">
      <Icon data={faLinkedin} scale="1.5" inverse label="linkedIn" />
    </a>
    <a
      title="resume"
      href="https://docs.google.com/document/d/1JluScSVuuTK9wMS2gK6ygd-4tRxCO73tvwR3lvDe1hI/edit?usp=sharing">
      <Icon data={faBriefcase} scale="1.5" inverse label="resume" />
    </a>
  </div>
  <a class="down" href="#about" title="next page">
    <Icon data={faChevronDown} scale="2" inverse label="next page" />
  </a>
</div>
