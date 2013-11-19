@string
Feature: String test

  @regex @delete
  Scenario: delete part of string via regex
    Given i have string "test-123"
    When I run regexp delete "!\d+!"
    Then I expect "test-"


  @regex @replace
  Scenario: replace part of string
    Given i have string "test-321"
    When I run regex replace from "!-\d+!" to "-test"
    Then I expect "test-test"