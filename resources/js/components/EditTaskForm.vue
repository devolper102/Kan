<template>
  <modal name="example" draggable=".window-header" :width="500"
         :height="165" :adaptive="true" :resizable="true" :clickToClose="false">
     <div class="window-header">
  <form
    class="relative mb-3 flex flex-col justify-between bg-white rounded-md shadow overflow-hidden"
    @submit.prevent="handleEditNewTask"
  >
    <div class="p-3 flex-1">
      <input
        class="block w-full px-2 py-1 text-lg border-b border-blue-800 rounded"
        type="text"
        placeholder="Enter a title"
        v-model.trim="newTask.title"
      />
      <textarea
        class="mt-3 p-2 block w-full p-1 border text-sm rounded"
        rows="2"
        placeholder="Add a description (optional)"
        v-model.trim="newTask.description"
      ></textarea>
      <div v-show="errorMessage">
        <span class="text-xs text-red-500">
          {{ errorMessage }}
        </span>
      </div>
    </div>
    <div class="p-3 flex justify-between items-end text-sm bg-gray-100">
      <button
        @click="$emit('task-canceled')"
        type="reset"
        class="py-1 leading-5 text-gray-600 hover:text-gray-700"
      >
        cancel
      </button>
      <button
        type="submit"
        class="px-3 py-1 leading-5 text-white bg-orange-600 hover:bg-orange-500 rounded"
      >
        Update
      </button>
    </div>
  </form>
  {{task.title}}
</div>
</modal>
</template>
<script>
export default {
  props: {
    statusId: Number,
    task: Object
  },
  data() {
    return {
      newTask: {
        title: this.task.title,
        description: this.task.description,
        status_id: this.task.id
      },
      errorMessage: ""
    };
  },
  mounted() {
    this.newTask.status_id = this.statusId;
    this.$modal.show('example')
  },
  methods: {
    handleEditNewTask() {
      // Basic validation so we don't send an empty task to the server
      if (!this.newTask.title) {
        this.errorMessage = "The title field is required";
        return;
      }

      // Send new task to server
      axios
        .post("/tasks/update", this.newTask)
        .then(res => {
          // Tell the parent component we've added a new task and include it
          console.log(res);
          this.$emit("task-edit", res.data[0]);
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    },
    handleErrors(err) {
      if (err.response && err.response.status === 422) {
        // We have a validation error
        const errorBag = err.response.data.errors;
        if (errorBag.title) {
          this.errorMessage = errorBag.title[0];
        } else if (errorBag.description) {
          this.errorMessage = errorBag.description[0];
        } else {
          this.errorMessage = err.response.message;
        }
      } else {
        // We have bigger problems
        console.log(err.response);
      }
    }
  }
};
</script>
