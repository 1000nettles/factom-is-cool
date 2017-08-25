window.Vue = require('vue');

/*
  Grab commands and populate them to the frontend dropdown
 */
var commands = Object.values(JSON.parse(commandsJson));
commands.unshift({ id: 0, identifier: 'Select a command' });

const commandsApp = new Vue({
    el: '#commands-app',

    data: {
      selectedCommand: 0,
      commands: commands
    },

    beforeMount() {
      this.setSelectedCommand();
    },

    methods: {
      setSelectedCommand: function() {
        if (lastCommand) {
          this.selectedCommand = Number(lastCommand);
        }
      }
    },

    computed: {
      currentCommandObj: function () {
        if (
          !this.selectedCommand
          || !this.commands[this.selectedCommand]
        ) {
          return null;
        }

        return this.commands[this.selectedCommand];
      },

      hasCommandParams: function () {
        if (
          !this.currentCommandObj
          || !this.currentCommandObj.commands_params
        ) {
          return false;
        }

        return this.currentCommandObj.commands_params.length > 0;
      },

      getDocumentationUrl: function() {
        let url = 'https://docs.factom.com/api';
        if (this.currentCommandObj) {
          url += '#' + this.currentCommandObj.identifier;
        }

        return url;
      },

      hasResultsJson: function() {
        return Boolean(resultsJson);
      },

      getResultsJson: function() {
        if (!this.hasResultsJson) {
          return false;
        }

        return JSON.stringify(JSON.parse(resultsJson), null, 4).trim();
      },

      getLastCommand: function() {
        if (!lastCommand) {
          return false;
        }

        return lastCommand;
      }
    }

});

