<template>
  <v-data-table
    :headers="headers"
    :items="desserts"
    sort-by="calories"
    class="elevation-1"
  >
    <template v-slot:top>
      <v-card-title
      >
        Таблицы:
      </v-card-title>
      <v-row background-color>
        <v-col
          v-for="(it, index) in tables"
          :key="index"
          cols="12"
          sm="6"
          md="4"
        >
          <v-card-title
            :color="it == currentTable?'Y':''"
            cursor
            :label="index"
            v-on:click="selectTable(it)"
          >{{it}}</v-card-title>
        </v-col>
      </v-row>

      <v-toolbar
        flat
      >
        <v-toolbar-title>{{currentTable}}</v-toolbar-title>
        <v-divider
          class="mx-4"
          inset
          vertical
        ></v-divider>
        <v-spacer></v-spacer>
        <v-dialog
          v-model="dialog"
          max-width="500px"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              color="primary"
              dark
              class="mb-2"
              v-bind="attrs"
              v-on="on"
            >
              New Item
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  <v-col
                    v-for="(it, index) in editedItem"
                    :key="index"
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-text-field
                      v-if="dataTable.rowsData[index]['Field'] != 'id'"
                      :background-color="dataTable.rowsData[index]['Null'] == 'YES'?'#F0F0F0':''"
                      v-model="editedItem[index]"
                      :label="index"
                    ></v-text-field>
                    <v-file-input
                      v-if="dataTable.rowsData[index]['Field'] == 'img'"
                      :background-color="dataTable.rowsData[index]['Null'] == 'YES'?'#F0F0F0':''"
                      v-model="editedItem[index]"
                      :label="index"
                    ></v-file-input>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="blue darken-1"
                text
                @click="close"
              >
                Cancel
              </v-btn>
              <v-btn
                color="blue darken-1"
                text
                @click="save"
              >
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">Cancel</v-btn>
              <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
    <template v-slot:item.actions="{ item }">
      <v-icon
        small
        class="mr-2"
        @click="editItem(item)"
      >
        mdi-pencil
      </v-icon>
      <v-icon
        small
        @click="deleteItem(item)"
      >
        mdi-delete
      </v-icon>
    </template>
    <template v-slot:no-data>
      <v-btn
        color="primary"
        @click="initialize"
      >
        Reset
      </v-btn>
    </template>
  </v-data-table>
</template>

<script>
  export default {
    data: () => ({
      currentTable: '',
      tables: [],
      dialog: false,
      dialogDelete: false,
      headers: [],
      desserts: [],
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
      dataTable: {}
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
      dialogDelete (val) {
        val || this.closeDelete()
      },
    },

    created () {
      this.initialize()
    },

    methods: {
      initialize () {
        this.getTables();
      },

      editRow(tableName ,row)
      {
        let myHeaders = new Headers();
        myHeaders.append("Authorization", "Bearer 4e53fc5b1831d2daabebacd838c61f9351e2f7c9254fecf5c1799232d4bc8feb");

        var formdata = new FormData();
        formdata.append("tableName",tableName);
        formdata = this.toFormData(row,formdata,'row')

        let requestOptions = {
          method: 'POST',
          headers: myHeaders,
          body: formdata,
        };

        fetch("http://localhost:8888/public/api/admin/editRow", requestOptions)
          .then(response => response.text())
          .then(result => JSON.parse(result))
          .then(obj => {
            if (!obj.success)
              console.log(obj.msgs);
            else
              console.log(true)
          })
          .catch(error => console.log('error', error));
      },

      toFormData(obj, form, namespace) {
        let fd = form || new FormData();
        let formKey;
        
        for(let property in obj) {
          if(obj[property]) {
            if (namespace) {
              formKey = namespace + '[' + property + ']';
            } else {
              formKey = property;
            }
           
            // if the property is an object, but not a File, use recursivity.
            if (obj[property] instanceof Date) {
              fd.append(formKey, obj[property].toISOString());
            }
            else if (typeof obj[property] === 'object' && !(obj[property] instanceof File)) {
              this.toFormData(obj[property], fd, formKey);
            } else { // if it's a string or a File object
              fd.append(formKey, obj[property]);
            }
          }
        }
        
        return fd;
      },

      deleteRow(tableName ,row)
      {
        let myHeaders = new Headers();
        myHeaders.append("Authorization", "Bearer 4e53fc5b1831d2daabebacd838c61f9351e2f7c9254fecf5c1799232d4bc8feb");

        var formdata = new FormData();
        formdata.append("tableName",tableName);
        formdata = this.toFormData(row,formdata,'row')

        let requestOptions = {
          method: 'POST',
          headers: myHeaders,
          body: formdata,
        };

        fetch("http://localhost:8888/public/api/admin/deleteRow", requestOptions)
          .then(response => response.text())
          .then(result => JSON.parse(result))
          .then(obj => {
            if (!obj.success)
              console.log(obj.msgs);
            else
              console.log(true)
          })
          .catch(error => console.log('error', error));
      },

      selectTable(name)
      {
        this.currentTable = name;
        this.getTable(this.currentTable, 0 , 1000, 1);
      },

      getTables()
      {
        let myHeaders = new Headers();
        myHeaders.append("Authorization", "Bearer 4e53fc5b1831d2daabebacd838c61f9351e2f7c9254fecf5c1799232d4bc8feb");

        let requestOptions = {
          method: 'POST',
          headers: myHeaders,
          redirect: 'follow'
        };

        fetch("http://localhost:8888/public/api/admin/getViewTables", requestOptions)
          .then(response => response.text())
          .then(result => JSON.parse(result))
          .then(obj => {
            if (!obj.success)
              console.log(obj.msgs);
            else
            {
              this.tables = obj.viewTables;
              this.currentTable = this.tables[0];
              this.getTable(this.currentTable, 0 , 1000, 1);
            }
          })
          .catch(error => console.log('error', error));
      },

      getTable(tableName = '', from = '', to = '', page = '')
      {
        let myHeaders = new Headers();
        myHeaders.append("Authorization", "Bearer 4e53fc5b1831d2daabebacd838c61f9351e2f7c9254fecf5c1799232d4bc8feb");

        var formdata = new FormData();
        formdata.append("tableName",tableName);
        formdata.append("from", from);
        formdata.append("to", to);
        formdata.append("page", page);

        let requestOptions = {
          method: 'POST',
          headers: myHeaders,
          body: formdata,
          redirect: 'follow'
        };

        fetch("http://localhost:8888/public/api/admin/getTable", requestOptions)
          .then(response => response.text())
          .then(result => JSON.parse(result))
          .then(obj => {
            if (!obj.success)
              console.log(obj.msgs);
            else
              this.setRowsData(obj.table);
          })
          .catch(error => console.log('error', error));
      },

      setRowsData(table)
      {
        this.desserts = table.rows;
        let headers = [];
        let defaultItem = {};

        for (let key in table.rowsData)
        {
          headers.push({text: table.rowsData[key]['Field'], value: table.rowsData[key]['Field'] });
          defaultItem[table.rowsData[key]['Field']] = table.rowsData[key]['Default'];
        }
        headers.push({ text: 'Actions', value: 'actions', sortable: false });

        this.dataTable = table;
        this.defaultItem = defaultItem; 
        this.headers = headers;
        this.editedItem = defaultItem;
      },

      editItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialog = true
      },

      deleteItem (item) {
        this.editedIndex = this.desserts.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialogDelete = true
      },

      deleteItemConfirm () {
        this.deleteRow(this.currentTable ,this.desserts[this.editedIndex])
        this.desserts.splice(this.editedIndex, 1)
        this.closeDelete()
      },

      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      closeDelete () {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      save () {
        this.editRow(this.currentTable ,this.editedItem);
        if (this.editedIndex > -1) {
          Object.assign(this.desserts[this.editedIndex], this.editedItem)
        } else {
          this.desserts.push(this.editedItem)
        }
        this.close()
      },
    },
    beforeMount() 
    {
    }
  }
</script>
<style type="text/css">
  *[background-color]{
    background-color: #ccc;
    margin-bottom: 40px;
  }
  *[color=Y]
  {
    color: #1976d2;
  }
  *[cursor]
  {
    border-right: 1px solid #000;
    cursor: pointer;
  }
</style>