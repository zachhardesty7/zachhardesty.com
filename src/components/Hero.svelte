<script>
  import { faChevronDown } from "@fortawesome/free-solid-svg-icons";
  import { Icons, Icon } from "../components";
  import { icons } from "../data";
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
    text-shadow: 0px 2px 3px var(--dark);
    font-family: "Roboto Condensed", sans-serif;
    font-size: 4.2rem;
    line-height: 110%;
    margin: 2.1rem 0 1.68rem 0;
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
  <Icons inverse {icons} />
  <Icon
    class="down"
    data={faChevronDown}
    inverse
    link="#about"
    title="next page"
  />
</div>
