<script>
  import { faChevronDown } from "@fortawesome/free-solid-svg-icons";
  import { experiences } from "../data";
</script>

<style>
  .content {
    margin-bottom: 2.5rem;
  }
  h4 {
    font-size: 1.64rem;
    line-height: 110%;
    margin: 0.82rem 0 0 0;
  }
  h3 {
    font-size: 2.28rem;
    line-height: 110%;
    margin: 0.5rem 0 0.5rem 0;
  }
  p {
    margin-top: 0px;
  }
  ul {
    padding-left: 2em;
  }
  li {
    list-style-type: disc;
  }
  li::marker {
    color: var(--primary);
    background-color: var(--primary);
  }
</style>

<div>
  {#each experiences as job (job.employer)}
    <div class="content">
      <h4 class="position">{`${job.position}`}</h4>
      <h3 class="employer">{`${job.employer} (${job.location})`}</h3>
      <p class="date">{job.date}</p>
      <ul>
        {#each job.bullets as bullet (bullet)}
          <li>{bullet}</li>
        {/each}
      </ul>
    </div>
  {/each}
</div>
