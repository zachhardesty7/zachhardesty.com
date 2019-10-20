<script>
  import Nav from "../components/Nav.svelte";

  export let segment;
</script>

<style>
  main {
    width: 70%;
    margin: auto;
    margin-top: 60px;
  }
</style>

<Nav {segment} />

<main>
  <slot />
</main>
