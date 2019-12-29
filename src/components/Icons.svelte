<script>
  import Icon from "../components/Icon.svelte";

  export let inverse;
  export let icons;
</script>

<style>
  div {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
  }

  div :global(a) {
    padding: 0 0.5em;
  }

  div :global(a:first-child) {
    padding-left: 0;
  }
  div :global(a:last-child) {
    padding-right: 0;
  }
</style>

<div>
  {#each icons as icon}
    <Icon {inverse} {...icon} />
  {/each}
</div>
