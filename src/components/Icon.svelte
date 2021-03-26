<script>
  import FAIcon from "svelte-awesome";

  let className;
  export { className as class };
  export let inverse;
  export let data;
  export let title;
  export let link;
  export let label = "";
</script>

<a
  class={className}
  {title}
  href={link}
  style="color: var(--{inverse ? 'secondary' : 'text'})"
>
  <FAIcon {data} scale="1.5" {inverse} label={title} />
  {#if label}
    <div>{label}</div>
  {/if}
</a>

<style>
  a {
    display: flex;
    flex-direction: column;
    align-items: center;

    opacity: 0.7;
    transition: opacity 0.3s ease;
  }

  a:hover {
    opacity: 1;
    transition: opacity 0.3s ease;
  }

  a div {
    margin-top: 0.5em;
  }
</style>
