<script>
  import { Icons } from "../components";
  import { icons } from "../data";
</script>

<footer>
  <div id="contact">
    <Icons inverse {icons} />
    <a href="mailto:hello@zachhardesty.com">hello@zachhardesty.com</a>
  </div>
  <p>designed and developed by zach hardesty · copyright 2021–2025 zach hardesty</p>
</footer>

<style>
  footer {
    margin-top: 1rem;
    background-color: rgb(0, 0, 0);
    background: linear-gradient(180deg, var(--primary), #000);
    grid-row: 3 / 3;
    grid-column: 1 / 4;
    font-size: 0.9rem;
    padding-top: 20px;
    color: var(--grey-0);
  }

  #contact {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
  }

  a {
    display: block;
    margin: auto;
    margin-top: 5px;
    width: fit-content;
    color: var(--light);
  }

  a:hover {
    color: var(--grey-0);
  }

  p {
    overflow: hidden;
    font-weight: 300;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  @media (prefers-color-scheme: dark) {
    footer {
      background: linear-gradient(180deg, var(--deep-navy), #000);
    }
  }
</style>
