export default {
  data() {
    return {
      deletingRow: false,
    };
  },

  methods: {
    confirmDeleteRow(id, index) {
      this.deletingRow = { id, index };
    },

    deleteRow(resourceRoute, message) {
      const id = this.deletingRow.id;
      message = message || __("Deleted");

      this.$axios
        .delete(cp_url(`${resourceRoute}/${id}`))
        .then(() => {
          let i = _.indexOf(this.items, _.findWhere(this.rows, { id }));
          this.items.splice(i, 1);
          this.deletingRow = false;
          this.$toast.success(message);

          location.reload();
        })
        .catch((e) => {
          this.$toast.error(
            e.response ? e.response.data.message : __("Something went wrong")
          );
        });
    },

    cancelDeleteRow() {
      this.deletingRow = false;
    },
  },
};