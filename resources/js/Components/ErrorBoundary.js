import React, { Component } from 'react';

class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = {
            error: null,
            errorInfo: null
        }
    }

    componentDidCatch(error, errorInfo) {
        this.setState({
            error: error,
            errorInfo: errorInfo
        });
    };

    render() {
        if (this.state.errorInfo) {
            return (
                <section className="col-md-24 p-0 mb-4" key='errorBoundary'>
                    <h2 className="form-title font-green col-md-24">Something went wrong...</h2>
                    <article>
                        <p>{this.state.error.message}</p>
                    </article>
                </section>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
