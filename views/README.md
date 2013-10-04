php_skeleton
=========================
php_minimal is the minimal Edmodo App: basic no-frills handling of install and launch endpoints.

=======
To run:
- Install this code on a server so that the 'launch' and 'index' endpoints are publicly visible (<yourserver.com>/launch runs launch/index.php).
- Get a developer account with Edmodo: https://www.edmodo.com/developers
- Create an App on your sandbox Edmodo instance, using the 'launch' and 'install' endpoints above for the corresponding fields in the App configuration.
- Copy the app id for your app to the 'apiKey' field of $REQUEST_CONFIGS in the function 'getAppConfig' in utils.php.
- Make sure your sandbox app is enabled and available in the Edmodo Store.
- Your developer account on the sandbox Edmodo instance should include user and password information for some sample teacher and student accounts.  Log in to your sandbox instance with one of those sample teacher accounts and install the App.
- On that same sample teacher account, go to the 'Apps' page and launch the app.

License
=======
Copyright 2013, Edmodo, Inc. 

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this work except in compliance with the License.
You may obtain a copy of the License in the LICENSE file, or at:

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.

Contributions
=======

We'd love for you to participate in the development of our project. Before we can accept your pull request, please sign our Individual Contributor License Agreement. It's a short form that covers our bases and makes sure you're eligible to contribute. Thank you!

http://goo.gl/gfj6Mb
