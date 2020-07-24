import { noAttendances } from "../../../fixtures/xhr-operations/attendances/no-attendances";
import { groups } from "../../../fixtures/xhr-operations/groups";
import { sets } from "../../../fixtures/xhr-operations/sets";
import { skills } from "../../../fixtures/xhr-operations/skills";
import { teams } from "../../../fixtures/xhr-operations/teams";
import { users } from "../../../fixtures/xhr-operations/users";
import { stubFetchingUserHeiims } from "../../../fixtures/xhr-operations/users/6/member";
import { stubSoulshrivenWithNoForumOauth } from "../../../fixtures/xhr-operations/users/@me/soulshriven";

describe('Roster Screen for Soulshriven user', function () {
    it('visits Roster page', function () {
        cy.server();
        stubSoulshrivenWithNoForumOauth(cy);
        groups(cy);
        sets(cy);
        skills(cy);
        teams(cy);
        users(cy);
        cy.route('GET', '/api/users/@me/characters', []);

        cy.visit('/');
        cy.contains('Roster').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users');
        cy.get('h2').should('have.text', 'Fetching Roster information...');
        cy.get('h2').should('have.text', 'Roster');
        cy.get('ul.roster > li').should('have.length', 34);

        cy.get('section:nth-of-type(1) > h3').should('have.text', 'Members (25)');
        cy.get('section:nth-of-type(1) > ul.roster > li').should('have.length', 25);

        cy.get('section:nth-of-type(2) > h3').should('have.text', 'Soulshriven (9)');
        cy.get('section:nth-of-type(2) > ul.roster > li').should('have.length', 9);

        stubFetchingUserHeiims(cy);
        noAttendances(cy);
        cy.contains('@HEIIMS').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users/6');
        cy.get('h2').should('have.text', '@HEIIMS');
        cy.get('dl:nth-of-type(1)').should('have.class', 'members');
        cy.get('dl:nth-of-type(2)').should('have.class', 'danger');
        cy.get('dl:nth-of-type(3)').should('have.class', 'info');
        cy.get('dl:nth-of-type(4)').should('have.class', '');
        cy.get('table.character-list-table > tbody > tr').should('have.length', 5);
        cy.get('table.character-list-table > tbody > tr.no-clearance').should('have.length', 1);
        cy.get('table.character-list-table > tbody > tr.tier-4').should('have.length', 4);
        cy.get('table.character-list-table > tbody > tr:first-of-type > td > ul.action-list').should('be.visible');
    });
});
