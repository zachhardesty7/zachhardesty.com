<script>
  import { faChevronDown } from "@fortawesome/free-solid-svg-icons";
  import { Icon, Icons } from "../components";
  import { icons } from "../data";
</script>

<div class="hero">
  <h1>zach hardesty</h1>
  <div class="container">
    <Icons inverse {icons} />
  </div>
  <Icon
    class="down"
    data={faChevronDown}
    inverse
    link="#about"
    title="next page"
  />
</div>

<style>
  .hero {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: var(--primary);
    text-align: center;
  }

  h1 {
    color: var(--secondary);
    text-shadow: 0px 2px 3px var(--dark);
    font-family: "Roboto Condensed", sans-serif;
    font-size: 3.73rem;
    line-height: 110%;
    margin: 1.87rem 0 1.49rem 0;
  }

  :global(.down) {
    position: absolute;
    bottom: 5%;
    left: 50%;
    right: 50%;
    width: fit-content;
    animation: bounce 4s ease-in-out infinite;
    transform: translateX(-50%);
  }
</style>
