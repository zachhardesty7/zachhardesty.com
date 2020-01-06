<script>
  import { Icons, Icon } from "../components";
  import { icons } from "../data";
</script>

<style>
  footer {
    background-color: rgb(0, 0, 0);
    background: linear-gradient(180deg, var(--primary), #000);
    grid-row: 3 / 3;
    grid-column: 1 / 4;
    font-size: 0.9rem;
    padding-top: 20px;
    color: #fff;
  }

  #contact {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
  }

  #contact a {
    display: block;
    margin: auto;
    width: fit-content;
    color: var(--light);
  }

  #contact a:hover {
    color: #fff;
  }

  p {
    overflow: hidden;
    font-weight: 300;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>

<footer>
  <div id="contact">
    <Icons inverse {icons} />
    <a href="mailto:hello@zachhardesty.com">hello@zachhardesty.com</a>
  </div>
  <p>website designed and developed by zach hardesty || copyright 2020</p>
</footer>
