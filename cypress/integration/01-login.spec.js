describe('Login Screen', function () {
    it('detects heading "Login"', function () {
        cy.visit('/');
        cy.get('h2').should('have.text', 'Login');
    });
});
