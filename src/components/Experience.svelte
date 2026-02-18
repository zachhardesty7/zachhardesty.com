<script>
  import { experiences } from "../data";
</script>

<div>
  {#each experiences as job (job.date)}
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

<style>
  .content {
    margin-bottom: 2.22rem;
  }
  .content:last-child {
    margin-bottom: 0;
  }
  h4 {
    font-size: 1.46rem;
    margin: 0.73rem 0 0 0;
  }
  h3 {
    color: var(--primary);
    margin: 0.44rem 0 0.44rem 0;
  }
  p {
    margin-top: 0px;
  }
  ul {
    padding-left: 1em;
  }
  li {
    list-style-type: disc;
  }
  li::marker {
    color: var(--primary);
    background-color: var(--primary);
  }

  @media (prefers-color-scheme: dark) {
    h3 {
      color: var(--grey-0);
    }
    li::marker {
      color: var(--accent);
      background-color: var(--accent);
    }
  }
</style>
