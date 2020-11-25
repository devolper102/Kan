<template>
<div>
  <div class="relative p-2 flex overflow-x-auto h-full">
    <!-- Columns (Statuses) -->  
    <div
      v-for="status in statuses"
      :key="status.slug"
      class="mr-6 w-4/5 max-w-xs flex-shrink-0"
    >
      <div class="rounded-md shadow-md overflow-hidden">
        <div class="p-3 flex justify-between items-baseline bg-blue-800 ">
          <h4 class="font-medium text-white">
            {{ status.title }}
          </h4>
           <button
            @click="DeleteCard(status.id)"
            class="py-1 px-2 text-sm text-orange-500 hover:underline"
          >
           Delete
          </button>
          <button
            @click="openAddTaskForm(status.id)"
            class="py-1 px-2 text-sm text-orange-500 hover:underline"
          >
            Add Task
          </button>
        </div>
        <div class="p-2 bg-blue-100">
          <!-- AddTaskForm -->
          
          <AddTaskForm
            v-if="newTaskForStatus === status.id"
            :status-id="status.id"
            v-on:task-added="handleTaskAdded"
            v-on:task-canceled="closeAddTaskForm"
          />

          <EditTaskForm
            v-if="newEditForStatus === status.id"
            :status-id="task_id"
            :task="task_data"
            v-on:task-edit="handleTaskEdit"
            v-on:task-canceled="closeEditTaskForm"
          />

          <!-- ./AddTaskForm -->

          <!-- Tasks -->
          <draggable
            class="flex-1 overflow-hidden"
            v-model="status.tasks"
            v-bind="taskDragOptions"
            @end="handleTaskMoved"
          >
            <transition-group
              class="flex-1 flex flex-col h-full overflow-x-hidden overflow-y-auto rounded shadow-xs"
              tag="div"
            >
              <div
                v-for="task in status.tasks"
                :key="task.id"
                class="mb-3 p-4 flex flex-col bg-white rounded-md shadow transform hover:shadow-md cursor-pointer"  @click="openEditTaskForm(status.id,task.id,task)"
              >
                <span class="block mb-2 text-xl text-gray-900">
                  {{ task.title }}
                </span>
                <!-- <p class="text-gray-700">
                  {{ task.description }}
                </p> -->
              </div>
              <!-- ./Tasks -->
            </transition-group>
          </draggable>
          <!-- No Tasks -->
          <div
            v-show="!status.tasks.length && newTaskForStatus !== status.id"
            class="flex-1 p-4 flex flex-col items-center justify-center"
          >
            <span class="text-gray-600">No tasks yet</span>
            <button
              class="mt-1 text-sm text-orange-600 hover:underline"
              @click="openAddTaskForm(status.id)"
            >
              Add one
            </button>
          </div>
          <!-- ./No Tasks -->
        </div>
      </div>
    </div>
     <div class="mr-6 w-4/5 max-w-xs flex-shrink-0">
      <div class="rounded-md shadow-md overflow-hidden">
        <div class="p-3 flex justify-between items-baseline bg-blue-800 ">
          <h4 class="font-medium text-white">
            Add new Card
          </h4>
         
           <button
            @click="openAddCardForm(statuses.length)"
            class="py-1 px-2 text-sm text-orange-500 hover:underline"
          >
            Add Card
          </button>
          <AddCardForm
            v-if="newForCard > 0"
            :card="newForCard"
            v-on:card-added="handleCardAdded"
            v-on:card-canceled="closeAddCardForm"
          />
          
        </div>
        <div class="p-2 bg-blue-100" style="min-height: 88px;">
        </div>
      </div>
    </div>
    <!-- ./Columns -->
  </div>
  <div class="relative p-2 flex overflow-x-auto h-full">
       <button
            @click="exportDB()"
            class="py-1 px-2 text-sm text-orange-500 hover:underline"
          >
            Export DB
          </button>
  </div>
</div>
</template>

<script>
import draggable from "vuedraggable";
import AddTaskForm from "./AddTaskForm";
import EditTaskForm from "./EditTaskForm";
import AddCardForm from "./AddCard";

export default {
  components: {draggable, AddTaskForm, EditTaskForm, AddCardForm},
  props: {
    initialData: Array
  },
  data() {
    return {
      statuses: [],
      newTaskForStatus: 0,
      newEditForStatus:0,
      newForCard:0,
      task_id:'',
      task_data :[]
    };
  },
  computed: {
    taskDragOptions() {
      return {
        animation: 200,
        group: "task-list",
        dragClass: "status-drag"
      };
    }
  },
  mounted() {
    // 'clone' the statuses so we don't alter the prop when making changes
    this.statuses = JSON.parse(JSON.stringify(this.initialData));
  },
  methods: {
    openAddTaskForm(statusId) {
      this.newTaskForStatus = statusId;
     },
     openAddCardForm(total) {

        this.newForCard = total+1;
    },
    openEditTaskForm(statusId, task_id ,task) {
      this.newEditForStatus = statusId;
      this.task_id = task_id;
      this.task_data = task;
    },
    closeAddTaskForm() {
      this.newTaskForStatus = 0;
    },
    closeAddCardForm() {
      this.newForCard = 0;
    },
    closeEditTaskForm() {
      this.newEditForStatus = 0;
    },
    handleTaskAdded(newTask) {
      // Find the index of the status where we should add the task
      const statusIndex = this.statuses.findIndex(
        status => status.id === newTask.status_id
      );

      // Add newly created task to our column
      this.statuses[statusIndex].tasks.push(newTask);

      // Reset and close the AddTaskForm
      this.closeAddTaskForm();
    },
    handleTaskEdit(editTask) {
      // Find the index of the status where we should update the task
       const statusIndex = this.statuses.findIndex(
        status => status.id === parseInt(editTask.status_id)
      );
      console.log(editTask,statusIndex,parseInt(editTask.status_id));
     const index =  this.statuses[statusIndex].tasks.findIndex(x => x.id === editTask.id);
      this.statuses[statusIndex].tasks[index] = editTask
      // Reset and close the EditTaskForm
      this.closeEditTaskForm();
    },
    handleCardAdded(newCard) {
      console.log(this.statuses,newCard);

      // Add newly created Card to our column
      this.statuses = newCard;

      // Reset and close the column
      this.closeAddCardForm();
     },
    handleTaskMoved(evt) {
      axios.put("/tasks/sync", { columns: this.statuses }).catch(err => {
        console.log(err.response);
      });
    },
    DeleteCard(evt)
    {
      axios.get("/statuses/delete/" + evt).then(res => {
        console.log(res)
         this.statuses = res.data;

      }).catch(err => {
        console.log(err.response);
      });
    },
    exportDB()
    {
      axios.get("/export" ).then(res => {
        if(res.data)
            {
              alert('Dump Created');
            }
         

      }).catch(err => {
        console.log(err.response);
      });
    }
  }
};
</script>

<style scoped>
.status-drag {
  transition: transform 0.5s;
  transition-property: all;
}
</style>
