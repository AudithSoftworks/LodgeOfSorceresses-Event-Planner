describe('Login Screen', function() {
    it('Inspects Login Screen', function() {
        cy.visit('/');
        cy.get('header > h1').should('have.css', 'background-image', 'url("http://planner.lodgeofsorceresses.test/images/logo.png")');
        cy.get('header > ul.member-bar > li > figure > figcaption').should('have.text', 'Welcome, Soulless One!');
        cy.get('header > ul.member-bar > li:nth-of-type(2)').contains('Forums');
        cy.get('header > ul.member-bar > li:nth-of-type(3)').contains('ESOLogs');
        cy.get('header > ul.member-bar > li:nth-of-type(4)').contains('Discord');
        cy.get('header > nav > ul.nav-tabs > li.nav-item > a').should('have.attr', 'title');
        cy.get('header > nav > ul.nav-tabs > li.nav-item > a > svg[data-icon="sign-in-alt"]').should('exist');
        cy.get('header > nav > ul.nav-tabs > li.nav-item > a').contains('Login');
        cy.get('h2').should('have.text', 'Login');
        cy.get('.react-notification-root').should('exist');
        cy.get('.react-notification-root ' +
            '> .notification-container-top-right ' +
            '> .notification-item-root ' +
            '> .notification-item ' +
            '> .notification-custom ' +
            '> .notification-content ' +
            '> p.notification-message')
            .should('exist')
            .should('have.text', 'Please login.');
    });
});
