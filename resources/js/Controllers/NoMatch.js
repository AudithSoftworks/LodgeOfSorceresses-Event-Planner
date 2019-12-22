import React, { PureComponent } from 'react';
import { Redirect } from "react-router-dom";

class NoMatch extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            error: null,
            info: null,
        };
    }

    componentDidCatch(error, info) {
        this.setState({
            error: error,
            info: info,
        });
    }

    render() {
        if (this.state.info) {
            return (
                <section className="col-md-24 p-0 mb-4" key="errorBoundary">
                    <h2 className="form-title font-green col-md-24">Something went wrong...</h2>
                    <article>
                        <p>{this.state.error.toString()}</p>
                    </article>
                </section>
            );
        }

        return <Redirect to={{ pathname: "/", state: { prevPath: location.pathname } }} />;
    }
}

export default NoMatch;
