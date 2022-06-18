<template>
<div class="d-flex mt-3 mb-2">
  <ul class="pagination ml-auto mr-auto">
    <li v-if="pagination.current_page > 1">
      <a href="javascript:void(0)" aria-label="Previous" v-on:click.prevent="changePage(pagination.current_page - 1)">
        <span aria-hidden="true">Previous</span>
      </a>
    </li>
    <li v-for="page in pagesNumber" :class="{'active': page == pagination.current_page}">
      <a href="javascript:void(0)" v-on:click.prevent="changePage(page)">{{ page }}</a>
    </li>
    <li v-if="pagination.current_page < pagination.last_page">
      <a href="javascript:void(0)" aria-label="Next" v-on:click.prevent="changePage(pagination.current_page + 1)">
        <span aria-hidden="true">Next</span>
      </a>
    </li>
  </ul>
</div>
</template>
<script>
  export default{
      props: {
      pagination: {
          type: Object,
          required: true
      },
      offset: {
          type: Number,
          default: 4
      }
    },
    computed: {
      pagesNumber() {
        let from = this.pagination.current_page - this.offset;
        if (from < 1) {
          from = 1;
        }
        let to = from + (this.offset * 2);
        if (to >= this.pagination.last_page) {
          to = this.pagination.last_page;
        }
        let pagesArray = [];
        for (let page = from; page <= to; page++) {
          pagesArray.push(page);
        }
          return pagesArray;
      }
    },
    methods : {
      changePage(page) {
        this.pagination.current_page = page;
        this.$emit('paginate');
      }
    }
  }
</script>
<style>
.pagination {
  display: flex;
}

.pagination a {
  color: #007bff;
  float: left;
  padding: 6px 15px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.pagination li.active a{
  background-color: #007bff;
  color: white;
  border: 1px solid #007bff;
}

.pagination li:first-child a {
    margin-left: 0;
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.pagination li:last-child a {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
.pagination a:hover:not(.active) {background-color: #ddd;}
</style>