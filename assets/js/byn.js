(function(){

    /* @View BuildYourNetwork
     * @description controls the UI for the Build Your Network Banner
     */

    var BuildYourNetwork = Backbone.View.extend({

        initialize: function(){

            var visible = [];

            this.$el = $('#promo-banner');
            this.views = [];         
            this.model = new BYOModel();

            //if the network isn't passed to the client we immediately destroy the banner
   
            try{

                this.network = new NetworkCollection(JSON.parse(modo.common.account.network));

            }catch(e){
                
                this.destroy();

            }
           
            //create views for the visible connections

            
            visible = this.network ? this.network.where({visible: 1}) : [];

            for(var i = 0, l = visible.length; i < l; i++){

                this.views.push(new UserView({model: visible[i], el: document.getElementById('connection-' + visible[i].get('id')), parent: this, network: this.network}));

            }

        },

        events: {

            'click #byn-invite-teachers':'showInvite',
            'click .close-promo-banner': 'destroy'

        },

        /* @method showInvite
         * @description show the invite teachers facebox
         */

        showInvite: function(e){

            modo.Analytics.pixel(e, 'lighthouse_suggestions', 'show_invite_teachers_popup', {user_id: modo.common.account.id});

            modo.classes.invitations.FBInviteTeachersView.trigger('show');

        },

        destroy: function(){

            this.model.save();    

        }

    });

    /* @View UserView
     * @description controls the functionality for each individual visible user
     */

    var UserView = Backbone.View.extend({

        initialize: function(params){
            
            this.container = this.$el.parent();

            //a reference to the network is passed
            this.network = params.network;
            this.listenTo(this.model, 'sync', this.onSync).listenTo(this.model, 'decline', this.onDecline);
            console.log(this.network);
            this.inviteReferral(e);

        },

        events: {

            'click .add-icon'  :'connect',
            'mouseenter'                  :'onMouseEnter',
            'mouseleave'                  :'onMouseLeave',
            'click .icon-close'           :'decline',
            'click .user-link'            :'showProfile',
            //'click .add-icon'    :'inviteReferral'


        },

        /* @method onMouseLeave
        /* @method onMouseLeave
         * @description adds a close class to the plus in the top right corner to show the closing x
         */

        onMouseEnter: function(){

            console.log('hi');
            this.$el.find('.icon').addClass('icon-close');
        },

        /* @method onMouseLeave
         * @description removes the .icon-close class to revert it back to the + in the top right corner
         */

        onMouseLeave: function(){

            this.$el.find('.icon').removeClass('icon-close');

        },

        /* @method connect
         * @description connect with the user
         */

        connect: function(e){

            console.log('yo');
            if($(e.currentTarget).hasClass('.icon') || $(e.currentTarget).parent().hasClass('sent')) return;

            modo.Analytics.pixel(e, 'lighthouse_suggestions', 'connect_with_suggestion', {user_id: modo.common.account.id, connection_id: this.model.get('id')});

            //send connection
            this.model.save();
            
            //removes connection from network model
            this.network.remove(this.model.get('id'));
            this.container.addClass('saving');
        
        },

        /* @method decline
         * @description declines the connection as a suggestion
         */

        decline: function(e){
           
            e.stopPropagation();
           
            modo.Analytics.pixel(e, 'lighthouse_suggestions', 'decline_suggestion', {user_id: modo.common.account.id, connection_id: this.model.get('id')});
 
            //declines the model 
            this.model.decline();
            
            //remove this model from the network
            this.network.remove(this.model.get('id'));

        },

        inviteReferral : function(e){

            console.log('hi');
            e.stopPropagation();

            this.model.invite();

            this.network.remove(this.model.get('id'));
            this.container.addClass('saving');

        },

        /* @method onSync
         * @description runs when connection request is sent successfully
        */

        onSync: function(){

            this.container.find('.add-icon').remove();
            this.container.removeClass('saving').addClass('sent');
            this.animateOut(4000);

        },

        /* @method onDecline
         * @description runs when a connection is declined successfuly
         */

        onDecline: function(){

            this.animateOut(0);

        },

        /* @method animateOut
         * @description begins process of animating a connection up and out
         * @param waitTime the time in milleseconds to wait before starting the animation
         */

        animateOut: function(waitTime){

            var self = this;

            self.$el.height(self.$el.outerHeight()).css('overflow', 'hidden');
            self.undelegateEvents();
            self.$el.parent().addClass('animating');            

            this.$el.find('.icon').remove();

            setTimeout(function(){

                var old = self.$el.height(0);
                
                self.addNew();

                setTimeout(function(){

                    old.remove();

                }, 1000);

            }, waitTime);
            
        },

        /* @method addNew
         * @description begins the process of showing a new connection
         */

        addNew: function(){

            var self = this;
            
            self.stopListening(self.model, 'sync', self.onSync).stopListening(this.model, 'decline', self.onDecline);;
            self.model = this.network.retrieveNew();

            //we ran out
            if(!self.model){

                self.removeContainer(self.container);
                return;

            }

            self.container.append(modo.Template.get('network-connection', self.model.toJSON()));
            self.$el = self.container.find('#connection-' + self.model.get('user_id')).css('overflow', 'hidden').height(0);

            self.delegateEvents();

            self.listenTo(self.model, 'sync', self.onSync).listenTo(self.model, 'decline', self.onDecline);

            setTimeout(function(){

                self.$el.height(147).removeClass('slide-added');
                self.container.removeClass('sent');

                setTimeout(function(){
                    
                    self.$el.removeAttr('style');
                    self.$el.parent().removeClass('animating');

                }, 500);

            }, 200);

        },

        /* @method removeContainer
         * @description removes container for connections, only happens if we run out of connections
         * @param container [JQElement] the <li> container
         */

        removeContainer: function(container){

            container.width(container.width());

            setTimeout(function(){ container.addClass('remove').width(0); }, 0);

        },

        showProfile: function(e){

            e.stopPropagation();

            modo.Analytics.pixel(e, 'lighthouse_suggestions', 'view_teacher_profile', {user_id: modo.common.account.id, connection_id: this.model.get('id')});

        }

    });
 
    /* @Model BYOModel
     * @description model for build your network view
     */

    var BYOModel = Backbone.Model.extend({

        url: '/new-user/ajax-save-user-events',

        defaults: {

            bar_type: 'lighthouse_suggestions'

        }

    });

    /* @Model UserModel
     * @description the model that contains data about each connection
     */

    var UserModel = Backbone.Model.extend({

        url: '/profile/ajax-connection-request',

        defaults: {

            avatar: '',
            edmodo_score: '0',
            first_name: '',
            last_name: '',
            name: '',
            school_id: '',
            thumb: '',
            title: '',
            user_id: '0',
            username: '',
            verified: '',
            visible: 0

        },

        initialize: function(data){

            this.set('id', data.user_id);

        },

        /* @method decline
         * @description declines this user
         */

        decline: function(){

            this.url = '/profile/ajax-decline-suggestion';

            $.ajax({

                url: '/profile/ajax-decline-suggestion',
                type: 'post',
                data: {object_id: this.get('user_id'), object_type: 1}

            });

            this.trigger('decline');

        },

        invite: function(){

            console.log(this.get('taxonomy_id'));
            this.url = '/invitations/ajax-create-invites'

            $.ajax({

                url: '/invitations/ajax-create-invites',
                type: 'post',
                data: {object_id: this.get('user_id'), object_type: 1}

            });

            this.trigger('invite');
        }

    });

    /* @Collection NetworkCollection
     * @description contains the UserModels in the network
     */

    var NetworkCollection = Backbone.Collection.extend({

        model: UserModel,

        /* @method retrieveNew
         * @description retrieves a new available UserModel and sets it to visible
         */

        retrieveNew: function(){

            var models = this.models;

            for(var i = 0, l = models.length; i < l; i++){

                if(models[i].get('visible') === 0){

                    models[i].set({'new':'slide-added', visible: 1});
                    return models[i];

                }

            }

        }

    });

    var BYNBanner = new BuildYourNetwork();

})()
