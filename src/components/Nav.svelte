<script>
  export let segment;

  const pages = ["about", "projects", "experience", "contact"];
</script>

<style>
  nav {
    top: 0;
    position: fixed;
    display: flex;
    width: 100%;
    align-items: center;
    z-index: 9;
    background-color: var(--primary);
  }

  ul > li:first-child {
    display: flex;
    flex: 1 1 auto;
    justify-content: flex-end;
  }
  ul > li:last-child {
    display: flex;
    flex: 1 1 auto;
    justify-content: flex-start;
  }

  ul {
    display: flex;
    width: 100%;
  }

  li {
    display: flex;
    align-items: center;
  }

  a {
    color: #fff;
    text-shadow: 0px 2px 4px black;
    padding: 5px;
  }

  a:hover {
    color: rgba(255, 255, 255, 0.75);
  }

  svg {
    vertical-align: middle;
    width: 50px !important;
    height: 50px !important;
    padding: 10px;
  }
  svg:not(:root) {
    overflow: hidden;
  }

  svg:hover path,
  svg:hover circle {
    stroke: rgba(255, 255, 255, 0.75);
  }

  svg path,
  svg circle {
    transition: stroke 0.3s ease;
  }
</style>

<nav>
  <ul>
    <li>
      {#each pages.slice(0, pages.length / 2) as page}
        <a
          class:active={segment === 'selected'}
          href="#{page}"
          offset={-60}
          tabIndex="0">
          {page}
        </a>
      {/each}
    </li>
    <li>
      <a class:active={segment === 'selected'} href="#title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 934 934">
          <path
            stroke-linejoin="round"
            d="M189.5 244.8l299.4-.7-299.5 299.4 555.6.5M487.5
            371.5v343.6m206.8-343.8v343.9"
            fill="none"
            stroke="#fff"
            stroke-linecap="round"
            stroke-width="60" />
          <circle
            cx="467"
            cy="467"
            r="437"
            fill="none"
            stroke="#fff"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-miterlimit="10"
            stroke-width="60" />
        </svg>
      </a>
    </li>

    <li>
      {#each pages.slice(pages.length / 2) as page}
        <a
          class:active={segment === 'selected'}
          href="#{page}"
          offset={-60}
          tabIndex="0">
          {page}
        </a>
      {/each}
    </li>
  </ul>
</nav>
