<script>
  import { Nav } from "../components";

  export let segment;
</script>

<Nav {segment} />

<main id="top">
  <slot />
</main>
