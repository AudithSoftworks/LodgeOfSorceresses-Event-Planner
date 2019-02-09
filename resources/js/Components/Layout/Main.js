import React, { Component } from 'react';
import { Route, Switch } from "react-router-dom";
import Characters from "../Characters";
import ErrorBoundary from "../ErrorBoundary";
import Events from "../Events";
import CharacterForm from "../Forms/CharacterForm";

class Main extends Component {
    render = () => {
        return [
            <main key="main" className="container">
                <ErrorBoundary>
                    <Switch>
                        <Route exact path="/" component={Characters}/>
                        <Route path="/events" component={Events}/>
                        <Route
                            path="/chars"
                            render={({match: {url}}) => (
                                <>
                                    <Route exact path={url} component={Characters}/>
                                    <Route path={url + '/create'} component={CharacterForm}/>
                                    <Route path={url + '/:id/edit'} component={CharacterForm}/>
                                </>
                            )}
                        />
                        <Route component={ErrorBoundary}/>
                    </Switch>
                </ErrorBoundary>
            </main>,
        ];
    };
}

export default Main;
