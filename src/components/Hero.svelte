<script>
  import {
    faBriefcase,
    faEnvelope,
    faChevronDown
  } from "@fortawesome/free-solid-svg-icons";
  import { faGithub, faLinkedin } from "@fortawesome/free-brands-svg-icons";

  import Icons from "../components/Icons.svelte";
  import Icon from "../components/Icon.svelte";

  import items from "../data/icons";
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

  :global(.down) {
    position: absolute;
    bottom: 5%;
    left: 50%;
    right: 50%;
    width: fit-content;
    animation: bounce 4s ease-in-out infinite;
  }
</style>

<div class="hero">
  <h1>zach hardesty</h1>
  <Icons inverse {items} />
  <Icon
    class="down"
    data={faChevronDown}
    inverse
    link="#about"
    title="next page"
  />
</div>
