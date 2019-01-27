import React, { Component } from 'react';
import { Route, Switch } from "react-router-dom";
import Characters from "../Characters";
import CharacterCreateForm from "../Forms/CharacterCreateForm";
import Events from "../Events";

class Main extends Component {
    render = () => {
        return (
            <main key="main" className="container">
                <Switch>
                    <Route exact path="/" component={Characters}/>
                    <Route exact path="/chars" component={Characters}/>
                    <Route path="/chars/create" component={CharacterCreateForm}/>
                    <Route path="/events" component={Events}/>
                </Switch>
            </main>
        );
    };
}

export default Main;
